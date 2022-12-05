<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Struct\ChecksumContainer;
use Ergonode\IntegrationShopware\Struct\ProductContainer;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;

class VariantsTransformer
{
    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private ChecksumContainer $checksumContainer;

    private ProductContainer $productContainer;

    public function __construct(
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        ChecksumContainer $checksumContainer,
        ProductContainer $productContainer
    ) {
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->checksumContainer = $checksumContainer;
        $this->productContainer = $productContainer;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if (false === $productData->ergonodeDataHasVariants()) {
            return $productData;
        }

        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $this->loadExistingVariants($productData, $context);

        $bindings = $ergonodeData['bindings'] ?? [];

        $transformedVariants = [];

        foreach ($ergonodeData['variantList']['edges'] ?? [] as $variantData) {
            $shopwareData = $variantData['node'];
            if (empty($shopwareData)) {
                continue;
            }

            $sku = $shopwareData['sku'];
            $existingProduct = $this->productContainer->get($sku);

            $dto = new ProductTransformationDTO($shopwareData);
            $dto->setBindingCodes(array_filter(array_map(fn(array $binding) => $binding['code'] ?? null, $bindings)));
            $dto->setSwProduct($existingProduct);

            $transformedVariants[$sku] = $this->productTransformerChain->transform(
                $dto,
                $context
            );
        }

        $entitiesToDelete = [];
        foreach ($transformedVariants as $variant) {
            $shopwareData = array_filter(
                $variant->getShopwareData(),
                fn($value) => !empty($value) || 0 === $value || false === $value
            );
            $swData['children'][] = $shopwareData;

            foreach ($shopwareData['options'] as $optionId) {
                if (
                    false === isset($swData['id']) ||
                    false === isset($optionId['id']) ||
                    $this->checksumContainer->exists($swData['id'], $optionId['id'])
                ) {
                    continue;
                }

                $swData['configuratorSettings'][] = [
                    'productId' => $swData['id'],
                    'optionId' => $optionId['id'],
                ];

                $this->checksumContainer->push($swData['id'], $optionId['id']);
            }

            $entitiesToDelete[] = $variant->getEntitiesToDelete();
        }

        $productData->setShopwareData($swData);

        $entitiesToDelete = array_merge_recursive(...$entitiesToDelete);
        foreach ($entitiesToDelete as $entityName => $payloads) {
            $productData->addEntitiesToDelete($entityName, $payloads);
        }

        $productData->addEntitiesToDelete(
            ProductDefinition::ENTITY_NAME,
            $this->getVariantsDeletePayload($productData)
        );

        $productData->addEntitiesToDelete(
            ProductConfiguratorSettingDefinition::ENTITY_NAME,
            $this->getConfiguratorSettingsDeletePayload($productData)
        );

        $this->checksumContainer->clear();

        return $productData;
    }

    private function loadExistingVariants(ProductTransformationDTO $productData, Context $context): void
    {
        if (false === $productData->swProductHasVariants()) {
            return;
        }

        $variants = $productData->getSwProduct()->getChildren();

        $skus = $variants->map(fn(ProductEntity $variant) => $variant->getProductNumber());
        if (empty($skus)) {
            return;
        }

        $entities = $this->productProvider->getProductsBySkuList($skus, $context, [
            'media',
            'properties',
            'options',
            'crossSellings.assignedProducts',
            'crossSellings.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        foreach ($entities as $productEntity) {
            $this->productContainer->set($productEntity->getProductNumber(), $productEntity);
        }
    }

    private function getVariantsDeletePayload(ProductTransformationDTO $productData): array
    {
        if (false === $productData->swProductHasVariants()) {
            return [];
        }

        $swProduct = $productData->getSwProduct();

        $newChildren = $productData->getShopwareData()['children'] ?? [];
        if (empty($newChildren)) {
            return [];
        }

        $currentVariantIds = $swProduct->getChildren()->getIds();
        $newVariantIds = array_filter(
            array_map(fn(array $child) => $child['id'] ?? null, $newChildren)
        );

        $idsToDelete = array_diff($currentVariantIds, $newVariantIds);

        return array_map(static fn($id) => ['id' => $id], array_values($idsToDelete));
    }

    private function getConfiguratorSettingsDeletePayload(ProductTransformationDTO $productData): array
    {
        $swProduct = $productData->getSwProduct();
        if (null === $swProduct) {
            return [];
        }

        $configuratorSettings = $swProduct->getConfiguratorSettings();
        if (null === $configuratorSettings) {
            return [];
        }

        $idsToDelete = $configuratorSettings->getIds();

        return array_map(static fn($id) => ['id' => $id], array_values($idsToDelete));
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Util\ChecksumContainer;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;

class VariantsTransformer
{
    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private ChecksumContainer $checksums;

    public function __construct(
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        ChecksumContainer $checksums
    ) {
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->checksums = $checksums;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if (false === $productData->ergonodeDataHasVariants()) {
            return $productData;
        }

        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $swVariants = $this->getExistingVariants($productData->getSwProduct(), $context);

        $bindings = $ergonodeData['bindings'] ?? [];

        $transformedVariants = [];

        foreach ($ergonodeData['variantList']['edges'] ?? [] as $variantData) {
            $shopwareData = $variantData['node'];
            if (empty($shopwareData)) {
                continue;
            }

            $sku = $shopwareData['sku'];
            $existingProduct = $swVariants[$sku] ?? null;

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
                    $this->checksums->exists($swData['id'], $optionId['id'])
                ) {
                    continue;
                }

                $swData['configuratorSettings'][] = [
                    'productId' => $swData['id'],
                    'optionId' => $optionId['id'],
                ];

                $this->checksums->push($swData['id'], $optionId['id']);
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

        $this->checksums->clear();

        return $productData;
    }

    /**
     * @return array<string, ProductEntity> <sku, ProductEntity>
     */
    private function getExistingVariants(?ProductEntity $product, Context $context): array
    {
        if (null === $product) {
            return [];
        }

        $variants = $product->getChildren();
        if (empty($variants)) {
            return [];
        }

        $skus = $variants->map(fn(ProductEntity $variant) => $variant->getProductNumber());
        if (empty($skus)) {
            return [];
        }

        $entities = $this->productProvider->getProductsBySkuList($skus, $context, [
            'media',
            'properties',
            'options',
            'crossSellings.assignedProducts',
            'crossSellings.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        $variants = [];
        foreach ($entities as $productEntity) {
            $variants[$productEntity->getProductNumber()] = $productEntity;
        }

        return $variants;
    }

    private function getVariantsDeletePayload(ProductTransformationDTO $dto): array
    {
        $swMainProduct = $dto->getSwProduct();
        if (null === $swMainProduct) {
            return [];
        }

        $newChildren = $dto->getShopwareData()['children'] ?? [];
        if (empty($newChildren)) {
            return [];
        }

        $currentVariants = $swMainProduct->getChildren();
        if (null === $currentVariants || 0 === $currentVariants->count()) {
            return [];
        }

        $currentVariantIds = $swMainProduct->getChildren()->getIds();
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
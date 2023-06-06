<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Struct\ChecksumContainer;
use Ergonode\IntegrationShopware\Struct\ProductContainer;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
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
        $parentProduct = $productData->getSwProduct();

        $this->loadExistingVariants($productData, $context);

        $bindings = $ergonodeData['bindings'] ?? [];

        $transformedVariants = [];

        foreach ($ergonodeData['variantList']['edges'] ?? [] as $variantData) {
            $variantNode = $variantData['node'];
            if (empty($variantNode)) {
                continue;
            }

            $sku = $variantNode['sku'];
            $existingProduct = $this->productContainer->get($sku);

            $dto = new ProductTransformationDTO($variantNode);
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


            if (null !== $parentProduct) {
                $shopwareData['parentId'] = $parentProduct->getId();
            }

            $swData['children'][] = $shopwareData;
            if (property_exists(ProductEntity::class, 'displayParent')) {
                $swData['displayParent'] = true;
            }

            foreach ($shopwareData['options'] as $optionId) {
                if (
                    false === isset($optionId['id']) ||
                    $this->checksumContainer->exists($swData['productNumber'], $optionId['id'])
                ) {
                    continue;
                }

                if (null !== $parentProduct) {
                    $existingConfigurationId = $this->getExistingConfigurationId($parentProduct, $optionId['id']);
                }

                $swData['configuratorSettings'][] = [
                    'id' => $existingConfigurationId ?? null,
                    'productId' => $swData['id'] ?? null,
                    'optionId' => $optionId['id'],
                ];

                $this->checksumContainer->push($swData['productNumber'], $optionId['id']);
            }

            $entitiesToDelete[] = $variant->getEntitiesToDelete();
        }

        $productData->setShopwareData($swData);

        $entitiesToDelete = array_merge_recursive(...$entitiesToDelete);

        foreach ($entitiesToDelete as $entityName => $payloads) {
            $productData->addEntitiesToDelete($entityName, $payloads);
        }

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

        $skus = [];

        $ergonodeVariants = $productData->getErgonodeData()['variantList']['edges'] ?? null;
        if (null !== $ergonodeVariants) {
            $skus = array_merge(
                $skus,
                array_filter(
                    array_map(fn(array $edge) => $edge['node']['sku'] ?? null, $ergonodeVariants)
                )
            );
        }

        $swVariants = $productData->getSwProduct()->getChildren();
        $skus = array_merge(
            $skus,
            $swVariants->map(fn(ProductEntity $variant) => $variant->getProductNumber())
        );

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

        if (!$productData->isInitialPaginatedImport()) {
            return [];
        }

        $idsToDelete = $configuratorSettings->getIds();

        return array_map(static fn($id) => ['id' => $id], array_values($idsToDelete));
    }

    private function getExistingConfigurationId(ProductEntity $product, string $optionId): ?string
    {
        foreach ($product->getConfiguratorSettings() as $configuratorSetting) {
            if ($configuratorSetting->getProductId() == $product->getId()
                && $configuratorSetting->getOptionId() == $optionId) {
                return $configuratorSetting->getId();
            }
        }

        return null;
    }
}

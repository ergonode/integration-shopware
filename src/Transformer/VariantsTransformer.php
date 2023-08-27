<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductShopwareData;
use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Struct\ChecksumContainer;
use Ergonode\IntegrationShopware\Struct\ProductContainer;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;

class VariantsTransformer
{
    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private ChecksumContainer $checksumContainer;

    private ProductContainer $productContainer;

    private LanguageProvider $languageProvider;

    public function __construct(
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        ChecksumContainer $checksumContainer,
        ProductContainer $productContainer,
        LanguageProvider $languageProvider
    ) {
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->checksumContainer = $checksumContainer;
        $this->productContainer = $productContainer;
        $this->languageProvider = $languageProvider;
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

        $defaultLanguage = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );
        foreach ($ergonodeData->getVariants() as $variantData) {
            $existingProduct = $this->productContainer->get($variantData->getSku());

            $dto = new ProductTransformationDTO($variantData, new ProductShopwareData([]), $defaultLanguage);
            $dto->setBindingCodes(array_filter(array_map(fn(array $binding) => $binding['code'] ?? null, $bindings)));
            $dto->setSwProduct($existingProduct);

            $transformedVariants[$variantData->getSku()] = $this->productTransformerChain->transform(
                $dto,
                $context
            );
        }

        $entitiesToDelete = [];
        foreach ($transformedVariants as $variant) {
            $shopwareData = $variant->getShopwareData();

            if (null !== $parentProduct) {
                $shopwareData->setParentId($parentProduct->getId());
            }

            $swData->addChild($shopwareData);
            if (property_exists(ProductEntity::class, 'displayParent')) {
                $swData->setDisplayParent();
            }

            foreach ($variant->getSwProduct()?->getOptionIds() ?? [] as $optionId) {
                if (
                    false === isset($optionId)
                    || $this->checksumContainer->exists($ergonodeData->getSku(), $optionId)
                ) {
                    continue;
                }

                if (null !== $parentProduct) {
                    $existingConfigurationId = $this->getExistingConfigurationId($parentProduct, $optionId);
                }

                $swData->addConfigrationSettings([
                    'id' => $existingConfigurationId ?? null,
                    'productId' => $productData->getSwProductId(),
                    'optionId' => $optionId,
                ]);

                $this->checksumContainer->push($ergonodeData->getSku(), $optionId);
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

        $ergonodeVariants = $productData->getErgonodeData()->getVariants();
        foreach ($ergonodeVariants as $ergonodeVariant) {
            $skus[] = $ergonodeVariant->getSku();
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

<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSellingAssignedProducts\ProductCrossSellingAssignedProductsDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionDefinition;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;
use Strix\Ergonode\Extension\ProductCrossSelling\ProductCrossSellingExtension;
use Strix\Ergonode\Manager\ExtensionManager;
use Strix\Ergonode\Provider\ConfigProvider;
use Strix\Ergonode\Provider\ProductCrossSellingProvider;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Util\CodeBuilderUtil;
use Strix\Ergonode\Util\ErgonodeApiValueKeyResolverUtil;

class ProductCrossSellingTransformer implements ProductDataTransformerInterface
{
    private const SW_PRODUCT_FIELD_CROSS_SELLING = 'crossSellings';

    private ConfigProvider $configProvider;

    private ProductProvider $productProvider;

    private TranslationTransformer $translationTransformer;

    private ProductCrossSellingProvider $productCrossSellingProvider;

    private ExtensionManager $extensionManager;

    public function __construct(
        ConfigProvider $configProvider,
        ProductProvider $productProvider,
        TranslationTransformer $translationTransformer,
        ProductCrossSellingProvider $productCrossSellingProvider,
        ExtensionManager $extensionManager
    ) {
        $this->configProvider = $configProvider;
        $this->productProvider = $productProvider;
        $this->translationTransformer = $translationTransformer;
        $this->productCrossSellingProvider = $productCrossSellingProvider;
        $this->extensionManager = $extensionManager;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();

        $codes = $this->configProvider->getErgonodeCrossSellingKeys();

        $attributes = array_values($this->getAttributesByCodes($productData->getErgonodeData(), $codes));

        $crossSellings = [];
        foreach ($attributes as $key => $ergoRelation) {
            $node = $ergoRelation['node'];
            $code = $node['attribute']['code'] ?? '';

            if (empty($code)) {
                continue;
            }

            // cross-selling in Shopware cannot be translatable; getting default language OR first one
            $value = $this->translationTransformer->transformDefaultLocale($node['valueTranslations'], $context);
            $skus = array_column($value, 'sku');

            $productIds = array_values($this->productProvider->getProductIdsBySkus($skus, $context));

            $existingCrossSelling = $this->getProductCrossSelling($productData->getSwProduct(), $code, $context);

            $assignedProducts = array_values(
                $this->getAssignedProductsPayload($existingCrossSelling, $productIds, $productData)
            );

            $crossSellings[] = [
                'id' => $existingCrossSelling ? $existingCrossSelling->getId() : null,
                'active' => true,
                'type' => ProductCrossSellingDefinition::TYPE_PRODUCT_LIST,
                'sortBy' => ProductCrossSellingDefinition::SORT_BY_NAME,
                'position' => $key,
                'assignedProducts' => $assignedProducts,
                'translations' => $this->translationTransformer->transform($node['attribute']['label'], 'name'),
                'extensions' => [
                    AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                        'id' => $existingCrossSelling ? $this->extensionManager->getEntityExtensionId($existingCrossSelling) : null,
                        'code' => CodeBuilderUtil::buildOptionCode($productData->getErgonodeData()['sku'], $code),
                        'type' => ProductCrossSellingExtension::ERGONODE_TYPE,
                    ],
                ],
            ];
        }

        if (!empty($crossSellings)) {
            $swData[self::SW_PRODUCT_FIELD_CROSS_SELLING] = $crossSellings;
        }

        $productData->setShopwareData($swData);

        $productData->addEntitiesToDelete(
            ProductCrossSellingDefinition::ENTITY_NAME,
            $this->getCrossSellingDeletePayload($productData)
        );

        // TODO remove after fixing delete cascade - SWERG-63
        $productData->addEntitiesToDelete(
            ErgonodeMappingExtensionDefinition::ENTITY_NAME,
            $this->getExtensionDeletePayload($productData)
        );

        return $productData;
    }

    private function getAttributesByCodes(array $ergonodeData, array $codes): array
    {
        return array_filter(
            $ergonodeData['attributeList']['edges'] ?? [],
            fn(array $attribute) => in_array($attribute['node']['attribute']['code'] ?? '', $codes)
        );
    }

    private function getProductCrossSelling(
        ?ProductEntity $swProduct,
        string $code,
        Context $context
    ): ?ProductCrossSellingEntity {
        if (null === $swProduct) {
            return null;
        }

        return $this->productCrossSellingProvider->getProductCrossSellingByMapping(
            $swProduct->getId(),
            CodeBuilderUtil::buildOptionCode($swProduct->getProductNumber(), $code),
            $context,
            ['assignedProducts']
        );
    }

    private function getCrossSellingDeletePayload(ProductTransformationDTO $productData): array
    {
        if (null === $productData->getSwProduct()) {
            return [];
        }

        $crossSellings = $productData->getSwProduct()->getCrossSellings();
        if (null === $crossSellings || 0 === $crossSellings->count()) {
            return [];
        }

        $swData = $productData->getShopwareData();
        $crossSellingIds = $crossSellings->getIds();

        if (empty($crossSellingIds)) {
            return [];
        }

        if (!isset($swData[self::SW_PRODUCT_FIELD_CROSS_SELLING])) {
            return array_map(fn(string $id) => ['id' => $id], $crossSellingIds);
        }

        $newCrossSellingIds = array_filter(
            array_map(fn(array $crossSelling) => $crossSelling['id'] ?? null, $swData[self::SW_PRODUCT_FIELD_CROSS_SELLING])
        );

        $idsToDelete = array_diff($crossSellingIds, $newCrossSellingIds);

        return array_map(fn(string $id) => ['id' => $id], $idsToDelete);
    }

    private function getExtensionDeletePayload(ProductTransformationDTO $productData): array
    {
        if (null === $productData->getSwProduct()) {
            return [];
        }

        $crossSellings = $productData->getSwProduct()->getCrossSellings();
        if (null === $crossSellings || 0 === $crossSellings->count()) {
            return [];
        }

        $swData = $productData->getShopwareData();
        $extensionIds = [];
        foreach ($crossSellings as $crossSelling) {
            $id = $this->extensionManager->getEntityExtensionId($crossSelling);
            if (null !== $id) {
                $extensionIds[] = $id;
            }
        }

        if (empty($extensionIds)) {
            return [];
        }

        if (!isset($swData[self::SW_PRODUCT_FIELD_CROSS_SELLING])) {
            return array_map(fn(string $id) => ['id' => $id], $extensionIds);
        }

        $newExtensionIds = array_filter(
            array_map(
                fn(array $crossSelling) => $crossSelling['extensions'][AbstractErgonodeMappingExtension::EXTENSION_NAME]['id'],
                $swData[self::SW_PRODUCT_FIELD_CROSS_SELLING]
            )
        );

        $idsToDelete = array_diff($extensionIds, $newExtensionIds);

        return array_map(fn(string $id) => ['id' => $id], $idsToDelete);
    }

    private function getAssignedProductsPayload(
        ?ProductCrossSellingEntity $existingCrossSelling,
        array $newProductIds,
        ProductTransformationDTO $productData
    ): array {
        $assignedProducts = [];
        foreach ($newProductIds as $index => $newProductId) {
            $assignedProducts[$newProductId] = [
                'productId' => $newProductId,
                'position' => $index,
            ];
        }

        if (null === $existingCrossSelling) {
            return $assignedProducts;
        }

        $idsToDelete = [];
        foreach ($existingCrossSelling->getAssignedProducts() ?? [] as $assignedProduct) {
            if (
                isset($assignedProducts[$assignedProduct->getProductId()]) &&
                !isset($assignedProducts[$assignedProduct->getProductId()]['id'])
            ) {
                $assignedProducts[$assignedProduct->getProductId()]['id'] = $assignedProduct->getId();
                continue;
            }
            $idsToDelete[] = $assignedProduct->getId();
        }

        $productData->addEntitiesToDelete(
            ProductCrossSellingAssignedProductsDefinition::ENTITY_NAME,
            array_map(fn(string $id) => ['id' => $id], $idsToDelete)
        );

        return $assignedProducts;
    }
}
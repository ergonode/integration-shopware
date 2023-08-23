<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\ProductCrossSelling\ProductCrossSellingExtension;
use Ergonode\IntegrationShopware\Manager\ExtensionManager;
use Ergonode\IntegrationShopware\Model\ProductRelationAttribute;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Provider\ProductCrossSellingProvider;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSellingAssignedProducts\ProductCrossSellingAssignedProductsDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductCrossSellingTransformer implements ProductDataTransformerInterface
{
    private const SW_PRODUCT_FIELD_CROSS_SELLING = 'crossSellings';

    private ConfigService $configService;

    private ProductProvider $productProvider;

    private ProductCrossSellingProvider $productCrossSellingProvider;

    private ExtensionManager $extensionManager;

    private LanguageProvider $languageProvider;

    private EntityRepository $mappingExtensionRepository;

    public function __construct(
        ConfigService $configService,
        ProductProvider $productProvider,
        ProductCrossSellingProvider $productCrossSellingProvider,
        ExtensionManager $extensionManager,
        LanguageProvider $languageProvider,
        EntityRepository $mappingExtensionRepository
    ) {
        $this->configService = $configService;
        $this->productProvider = $productProvider;
        $this->productCrossSellingProvider = $productCrossSellingProvider;
        $this->extensionManager = $extensionManager;
        $this->languageProvider = $languageProvider;
        $this->mappingExtensionRepository = $mappingExtensionRepository;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if ($productData->isVariant()) {
            return $productData; // cross-selling for variants is not supported by Shopware
        }

        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $codes = $this->configService->getErgonodeCrossSellingKeys();

        $attributes = [];
        foreach ($codes as $code) {
            $attribute = $ergonodeData->getAttributeByCode($code);
            if ($attribute instanceof ProductRelationAttribute) {
                $attributes[] = $attribute;
            }
        }

        $crossSellings = [];
        $position = 0;
        foreach ($attributes as $relationAttribute) {
            $defaultLocale = $this->languageProvider->getDefaultLanguageLocale($context);
            $defaultTranslation = $relationAttribute->getTranslation(
                IsoCodeConverter::shopwareToErgonodeIso($defaultLocale)
            );
            $skus = $defaultTranslation?->getValue();
            if (empty($skus)) {
                continue;
            }
            $productIds = array_values($this->productProvider->getProductIdsBySkus($skus, $context));

            $existingCrossSelling = $this->getProductCrossSelling($productData->getSwProduct(), $code, $context);

            $assignedProducts = array_values(
                $this->getAssignedProductsPayload($existingCrossSelling, $productIds, $productData)
            );

            $translations[$defaultLocale] = [
                'name' => $code,
            ];

            $extensionCode = CodeBuilderUtil::buildExtended($productData->getErgonodeData()->getSku(), $code);
            if (!$existingCrossSelling) {
                $this->deleteLegacyMapping($extensionCode, $context);
            }

            $crossSellings[] = [
                'id' => $existingCrossSelling?->getId(),
                'active' => true,
                'type' => ProductCrossSellingDefinition::TYPE_PRODUCT_LIST,
                'sortBy' => ProductCrossSellingDefinition::SORT_BY_NAME,
                'position' => $position,
                'assignedProducts' => $assignedProducts,
                'translations' => $translations,
                'extensions' => [
                    AbstractErgonodeMappingExtension::EXTENSION_NAME => [
                        'id' => $existingCrossSelling ? $this->extensionManager->getEntityExtensionId(
                            $existingCrossSelling
                        ) : null,
                        'code' => $extensionCode,
                        'type' => ProductCrossSellingExtension::ERGONODE_TYPE,
                    ],
                ],
            ];
            $position++;
        }

        $swData->setCrossSellings($crossSellings);

        $productData->setShopwareData($swData);

        $productData->addEntitiesToDelete(
            ProductCrossSellingDefinition::ENTITY_NAME,
            $this->getCrossSellingDeletePayload($productData)
        );

        return $productData;
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
            CodeBuilderUtil::buildExtended($swProduct->getProductNumber(), $code),
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

        if ($swData->getCrossSellings()) {
            return array_map(fn(string $id) => ['id' => $id], $crossSellingIds);
        }

        $newCrossSellingIds = array_filter(
            array_map(fn(array $crossSelling) => $crossSelling['id'] ?? null,
                $swData->getCrossSellings())
        );

        $idsToDelete = array_diff($crossSellingIds, $newCrossSellingIds);

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
                isset(
                    $assignedProducts[$assignedProduct->getProductId()]
                )
                && !isset($assignedProducts[$assignedProduct->getProductId()]['id'])
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

    private function deleteLegacyMapping(string $extensionCode, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $extensionCode));
        $criteria->addFilter(new EqualsFilter('type', ProductCrossSellingExtension::ERGONODE_TYPE));
        $ids = $this->mappingExtensionRepository->searchIds($criteria, $context)->getIds();
        if (!empty($ids)) {
            $this->mappingExtensionRepository->delete(array_map(static fn($id) => ['id' => $id], $ids), $context);
        }
    }
}

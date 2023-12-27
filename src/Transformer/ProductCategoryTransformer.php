<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;

use function array_merge;
use function array_values;

class ProductCategoryTransformer implements ProductDataTransformerInterface
{
    private CategoryProvider $categoryProvider;

    private ConfigService $configService;

    public function __construct(CategoryProvider $categoryProvider, ConfigService $configService)
    {
        $this->categoryProvider = $categoryProvider;
        $this->configService = $configService;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if ($this->configService->isProductCategoryAssignDisabled()) {
            return $productData;
        }
        $categoryData = $productData->getErgonodeData()->getCategories();

        $categoryIds = [];
        foreach ($categoryData as $category) {
            $categoryCode = $category['node']['code'] ?? null;
            if (null === $categoryCode) {
                continue;
            }

            $categoryCollection = $this->categoryProvider->getCategoriesByCode($categoryCode, $context);

            $categoryIds[] = $categoryCollection->map(
                static fn(CategoryEntity $categoryEntity) => ['id' => $categoryEntity->getId()]
            );
        }

        $categoryIds = array_merge(...$categoryIds);

        $swData = $productData->getShopwareData();
        $swData->setCategories(array_values($categoryIds));
        $productData->setShopwareData($swData);

        return $productData;
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;

class ProductCategoryTransformer implements ProductDataTransformerInterface
{
    private CategoryProvider $categoryProvider;

    public function __construct(CategoryProvider $categoryProvider)
    {
        $this->categoryProvider = $categoryProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $categoryData = $productData->getErgonodeData()['categoryList']['edges'] ?? null;

        if (null === $categoryData) {
            return $productData;
        }

        $categoryIds = [];
        foreach ($categoryData as $category) {
            $categoryCode = $category['node']['code'] ?? null;
            if (null === $categoryCode) {
                continue;
            }

            $categoryCollection = $this->categoryProvider->getCategoriesByCode($categoryCode, $context);

            $categoryIds = \array_merge(
                $categoryIds,
                $categoryCollection->map(
                    static fn(CategoryEntity $categoryEntity) => ['id' => $categoryEntity->getId()]
                )
            );
        }

        $swData = $productData->getShopwareData();
        $swData['categories'] = \array_values($categoryIds);
        $productData->setShopwareData($swData);

        return $productData;
    }
}
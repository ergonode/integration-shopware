<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ProductCategoryResultsProxy extends AbstractResultsProxy
{
    public const MAIN_FIELD = 'product';

    public const CATEGORY_LIST_FIELD = 'productCategory';

    public function getProductData(): array
    {
        return $this->getMainData();
    }

    public function hasCategoriesNextPage(): bool
    {
        return (bool) ($this->getCategories()['pageInfo']['hasNextPage'] ?? false);
    }

    public function getCategoriesEndCursor(): ?string
    {
        return $this->getCategories()['pageInfo']['endCursor'] ?? null;
    }

    public function getCategories(): array
    {
        return $this->getMainData()['categoryList'] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ProductResultsProxy extends AbstractResultsProxy
{
    public const MAIN_FIELD = 'product';

    public function getProductData(): array
    {
        return $this->getMainData();
    }

    public function hasVariantsNextPage(): bool
    {
        return (bool) ($this->getVariants()['pageInfo']['hasNextPage'] ?? false);
    }

    public function getVariantsEndCursor(): ?string
    {
        return $this->getVariants()['pageInfo']['endCursor'] ?? null;
    }

    public function getVariants(): array
    {
        return $this->getMainData()['variantList'] ?? [];
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

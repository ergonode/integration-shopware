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

    public function hasNextPage(): bool
    {
        return (bool) ($this->getVariants()['pageInfo']['hasNextPage'] ?? false);
    }

    public function getEndCursor(): ?string
    {
        return $this->getVariants()['pageInfo']['endCursor'] ?? null;
    }

    public function getVariants(): array
    {
        return $this->getMainData()['variantList'];
    }
}

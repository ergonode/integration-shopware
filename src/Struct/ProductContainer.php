<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Struct;

use Shopware\Core\Content\Product\ProductEntity;

class ProductContainer
{
    /**
     * @var ProductEntity[]
     */
    private array $products = [];

    public function set(string $sku, ProductEntity $value): void
    {
        $this->products[$sku] = $value;
    }

    public function get(string $sku): ?ProductEntity
    {
        return $this->products[$sku] ?? null;
    }

    public function clear(): void
    {
        $this->products = [];
    }
}
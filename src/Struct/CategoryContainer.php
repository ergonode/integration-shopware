<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Struct;


class CategoryContainer
{
    /** @var string[] */
    private array $categories = [];

    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    public function getShopwareId(string $ergonodeCategoryCode): ?string
    {
        return $this->categories[$ergonodeCategoryCode] ?? null;
    }

    public function clear(): void
    {
        $this->categories = [];
    }
}
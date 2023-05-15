<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMapping;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeCategoryMappingEntity extends Entity
{
    use EntityIdTrait;

    protected string $shopwareId;

    protected string $ergonodeKey;

    protected $category;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $categoryEntity)
    {
        $this->category = $categoryEntity;
    }

    public function getShopwareId(): string
    {
        return $this->shopwareId;
    }

    public function setShopwareId(string $shopwareId): void
    {
        $this->shopwareId = $shopwareId;
    }

    public function getErgonodeKey(): string
    {
        return $this->ergonodeKey;
    }

    public function setErgonodeKey(string $ergonodeKey): void
    {
        $this->ergonodeKey = $ergonodeKey;
    }
}

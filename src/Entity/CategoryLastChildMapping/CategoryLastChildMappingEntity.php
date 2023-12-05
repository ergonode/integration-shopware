<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CategoryLastChildMappingEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $categoryId = null;

    protected string $lastChildId;

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getLastChildId(): string
    {
        return $this->lastChildId;
    }

    public function setLastChildId(string $lastChildId): void
    {
        $this->lastChildId = $lastChildId;
    }
}

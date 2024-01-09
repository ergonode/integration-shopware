<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeCategoryAttributeMappingEntity extends Entity
{
    use EntityIdTrait;

    protected string $shopwareKey;

    protected string $ergonodeKey;

    protected ?bool $active;

    public function getShopwareKey(): string
    {
        return $this->shopwareKey;
    }

    public function setShopwareKey(string $shopwareKey): void
    {
        $this->shopwareKey = $shopwareKey;
    }

    public function getErgonodeKey(): string
    {
        return $this->ergonodeKey;
    }

    public function setErgonodeKey(string $ergonodeKey): void
    {
        $this->ergonodeKey = $ergonodeKey;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }
}

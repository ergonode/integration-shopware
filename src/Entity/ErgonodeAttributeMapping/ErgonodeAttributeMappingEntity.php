<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeAttributeMappingEntity extends Entity
{
    use EntityIdTrait;

    protected string $shopwareKey;

    protected string $ergonodeKey;

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
}

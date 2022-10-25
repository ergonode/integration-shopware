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

    protected bool $castToBool = false;

    /**
     * @return string
     */
    public function getShopwareKey(): string
    {
        return $this->shopwareKey;
    }

    /**
     * @param string $shopwareKey
     */
    public function setShopwareKey(string $shopwareKey): void
    {
        $this->shopwareKey = $shopwareKey;
    }

    /**
     * @return string
     */
    public function getErgonodeKey(): string
    {
        return $this->ergonodeKey;
    }

    /**
     * @param string $ergonodeKey
     */
    public function setErgonodeKey(string $ergonodeKey): void
    {
        $this->ergonodeKey = $ergonodeKey;
    }

    public function isCastToBool(): bool
    {
        return $this->castToBool;
    }

    public function setCastToBool(bool $castToBool): void
    {
        $this->castToBool = $castToBool;
    }
}

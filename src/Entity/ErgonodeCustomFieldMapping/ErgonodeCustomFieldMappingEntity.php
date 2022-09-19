<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCustomFieldMapping;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeCustomFieldMappingEntity extends Entity
{
    use EntityIdTrait;

    protected string $shopwareKey;

    protected string $ergonodeKey;

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
}

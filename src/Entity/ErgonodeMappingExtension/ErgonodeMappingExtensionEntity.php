<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeMappingExtensionEntity extends Entity
{
    use EntityIdTrait;

    protected string $code;

    protected string $type;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
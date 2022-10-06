<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ErgonodeCategoryMappingExtensionEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $code;

    protected ?string $treeCode;

    protected ?string $locale;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getTreeCode(): ?string
    {
        return $this->treeCode;
    }

    public function setTreeCode(?string $treeCode): void
    {
        $this->treeCode = $treeCode;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
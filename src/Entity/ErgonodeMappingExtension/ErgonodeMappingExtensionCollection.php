<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeMappingExtensionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeMappingExtensionEntity::class;
    }
}
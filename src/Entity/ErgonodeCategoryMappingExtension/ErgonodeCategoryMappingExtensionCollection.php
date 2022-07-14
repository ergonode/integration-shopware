<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeCategoryMappingExtensionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCategoryMappingExtensionEntity::class;
    }
}
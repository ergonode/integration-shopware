<?php

declare(strict_types=1);

namespace Strix\Ergonode\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeCategoryMappingExtensionEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCategoryMappingExtensionEntity::class;
    }
}
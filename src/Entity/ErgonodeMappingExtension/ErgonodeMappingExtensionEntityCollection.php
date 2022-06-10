<?php

declare(strict_types=1);

namespace Strix\Ergonode\Entity\ErgonodeMappingExtension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeMappingExtensionEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeMappingExtensionEntity::class;
    }
}
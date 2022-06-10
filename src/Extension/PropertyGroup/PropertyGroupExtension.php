<?php

declare(strict_types=1);

namespace Strix\Ergonode\Extension\PropertyGroup;

use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;

class PropertyGroupExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'attribute';

    public function getDefinitionClass(): string
    {
        return PropertyGroupDefinition::class;
    }
}
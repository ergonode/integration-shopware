<?php

declare(strict_types=1);

namespace Strix\Ergonode\Extension\PropertyGroupOption;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;

class PropertyGroupOptionExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'attribute_option';

    public function getDefinitionClass(): string
    {
        return PropertyGroupOptionDefinition::class;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Extension\PropertyGroupOption;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;

class PropertyGroupOptionExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'attribute_option';

    public function getDefinitionClass(): string
    {
        return PropertyGroupOptionDefinition::class;
    }
}
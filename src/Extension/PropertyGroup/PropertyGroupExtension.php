<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Extension\PropertyGroup;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Content\Property\PropertyGroupDefinition;

class PropertyGroupExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'attribute';

    public function getDefinitionClass(): string
    {
        return PropertyGroupDefinition::class;
    }
}
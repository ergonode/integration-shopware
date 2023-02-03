<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Extension\Unit;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\System\Unit\UnitDefinition;

class UnitExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'unit';

    public function getDefinitionClass(): string
    {
        return UnitDefinition::class;
    }
}

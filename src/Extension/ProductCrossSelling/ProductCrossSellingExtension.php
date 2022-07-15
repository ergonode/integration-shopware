<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Extension\ProductCrossSelling;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;

class ProductCrossSellingExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'product_attribute';

    public function getDefinitionClass(): string
    {
        return ProductCrossSellingDefinition::class;
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Extension\ProductCrossSelling;

use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;

class ProductCrossSellingExtension extends AbstractErgonodeMappingExtension
{
    public const ERGONODE_TYPE = 'product_attribute';

    public function getDefinitionClass(): string
    {
        return ProductCrossSellingDefinition::class;
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;

class ProductMediaPersistor
{
    public function __construct()
    {
    }

    public function persist(ProductResultsProxy $results, Context $context): array
    {
        $entities = $this->persistProductMedia($results->getProductData(), $context);

        foreach ($results->getVariants() as $variantData) {
            $entities = array_merge_recursive(
                $entities,
                $this->persistProductMedia($variantData['node'], $context)
            );
        }

        return $entities;
    }

    private function persistProductMedia($node, Context $context): array
    {


        return [];
    }
}
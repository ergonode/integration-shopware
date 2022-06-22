<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    public function transform(array $productData, Context $context): array
    {
        $productData['price'] = [
            \array_merge(
                $productData['price'],
                [
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY
                ]
            )
        ];

        return $productData;
    }
}
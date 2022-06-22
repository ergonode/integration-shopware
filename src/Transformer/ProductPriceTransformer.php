<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $swData['price'] = [
            \array_merge(
                $swData['price'],
                [
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY
                ]
            )
        ];

        $productData->setShopwareData($swData);

        return $productData;
    }
}
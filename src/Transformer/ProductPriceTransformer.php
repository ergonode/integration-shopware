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
        if ($productData->isUpdate()) {
            return $productData;
        }

        $swData = $productData->getShopwareData();
        $swData['price'] = [
            [
                'net' => 0,
                'gross' => 0,
                'linked' => false,
                'currencyId' => Defaults::CURRENCY
            ]
        ];

        $productData->setShopwareData($swData);

        return $productData;
    }
}
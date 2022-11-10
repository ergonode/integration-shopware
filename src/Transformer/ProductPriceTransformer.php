<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

use function array_merge;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $swData['price'] = [
            array_merge(
                [
                    'gross' => 0,
                    'net' => 0,
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY
                ],
                $swData['price'] ?? []
            )
        ];

        $productData->setShopwareData($swData);

        return $productData;
    }
}
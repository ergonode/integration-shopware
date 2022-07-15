<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Framework\Context;

class ProductDefaultValuesTransformer implements ProductDataTransformerInterface
{
    private const DEFAULT_STOCK_VALUE = 999;

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if ($productData->isUpdate()) {
            return $productData;
        }

        $swData = $productData->getShopwareData();
        $swData['name'] = $swData['name'] ?? $productData->getErgonodeData()['sku'];
        $swData['stock'] = $swData['stock'] ?? self::DEFAULT_STOCK_VALUE;

        $productData->setShopwareData($swData);

        return $productData;
    }
}
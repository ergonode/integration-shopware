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
        $swData = $productData->getShopwareData();
        $sku = $productData->getErgonodeData()->getSku();
        $swData['productNumber'] = $sku;

        if ($productData->isCreate()) {
            $swData->setName($swData->getName() ?? $sku);
            $swData->setStock($swData->getStock() ?? self::DEFAULT_STOCK_VALUE);
        }

        $productData->setShopwareData($swData);

        return $productData;
    }
}

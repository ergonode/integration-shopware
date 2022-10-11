<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use RuntimeException;
use Shopware\Core\Framework\Context;

class ProductDefaultValuesTransformer implements ProductDataTransformerInterface
{
    private const DEFAULT_STOCK_VALUE = 999;

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $sku = $productData->getErgonodeData()['sku'] ?? null;

        if (null === $sku) {
            throw new RuntimeException('Missing SKU from product data');
        }

        $swData['productNumber'] = $sku;

        if ($productData->isCreate()) {
            $swData['name'] = $swData['name'] ?? $sku;
            $swData['stock'] = $swData['stock'] ?? self::DEFAULT_STOCK_VALUE;
        }

        $productData->setShopwareData($swData);

        return $productData;
    }
}

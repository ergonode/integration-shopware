<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\Strategy;

use Ergonode\IntegrationShopware\DTO\ProductShopwareData;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;

class ProductResetValueStrategy
{
    // if value is unset in Ergonode, in Shopware it should be unchanged
    private const UNCHANGED_FIELDS = [
        'active',
        'name',
        'price_net',
        'price_gross',
        'stock',
    ];

    // if value is unset in Ergonode, in Shopware it should be changed to 1
    private const VALUE_1_FIELDS = [
        'minPurchase',
        'purchaseSteps',
    ];

    // if value is unset in Ergonode, in Shopware it should be changed to false
    private const VALUE_FALSE_FIELDS = [
        'isCloseout',
        'shippingFree',
        'markAsTopseller',
    ];
    public function resetValue(
        ProductShopwareData $shopwareData,
        ErgonodeAttributeMappingEntity $mapping
    ): ProductShopwareData {
        if (in_array($mapping->getShopwareKey(), self::UNCHANGED_FIELDS)) {
            return $shopwareData;
        }

        if (in_array($mapping->getShopwareKey(), self::VALUE_1_FIELDS)) {
            $shopwareData->setData($mapping->getShopwareKey(), 1);

            return $shopwareData;
        }

        if (in_array($mapping->getShopwareKey(), self::VALUE_FALSE_FIELDS)) {
            $shopwareData->setData($mapping->getShopwareKey(), false);

            return $shopwareData;
        }

        $shopwareData->setData($mapping->getShopwareKey(), null);

        return $shopwareData;
    }
}

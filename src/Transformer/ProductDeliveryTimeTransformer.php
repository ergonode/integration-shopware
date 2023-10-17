<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\DeliveryTimeProvider;
use Shopware\Core\Framework\Context;

class ProductDeliveryTimeTransformer implements ProductDataTransformerInterface
{
    private DeliveryTimeProvider $deliveryTimeProvider;

    public function __construct(
        DeliveryTimeProvider $deliveryTimeProvider
    ) {
        $this->deliveryTimeProvider = $deliveryTimeProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $deliveryTime = $ergonodeData->getDeliveryTime();
        if ($deliveryTime) {
            $shopwareData->setDeliveryTimeId(null);
            if ($deliveryTime->getFirstOption()) {
                $name = $deliveryTime->getFirstOption()->getName();
                $productDeliveryTime = $name[$productData->getDefaultLanguage()] ?? $deliveryTime->getFirstOption()->getCode();
                $deliveryTimeId = $this->deliveryTimeProvider->getIdByName($productDeliveryTime, $context);
                $shopwareData->setDeliveryTimeId($deliveryTimeId);
            }
        } elseif ($deliveryTime === false && $productData->getSwProduct()) {
            $shopwareData->setDeliveryTimeId($productData->getSwProduct()->getDeliveryTimeId());
        } else {
            $shopwareData->setDeliveryTimeId(null);
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }
}

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
        $swData = $productData->getShopwareData();
        $productDeliveryTime = $swData['deliveryTime'] ?? null;
        if (!empty($productDeliveryTime)) {
            $deliveryTimeId = $this->deliveryTimeProvider->getIdByName($productDeliveryTime, $context);
            $swData['deliveryTimeId'] = $deliveryTimeId;
        }

        unset($swData['deliveryTime']);
        $productData->setShopwareData($swData);

        return $productData;
    }
}

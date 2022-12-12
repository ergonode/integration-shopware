<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\DeliveryTimeProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

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
        $productDeliveryTime = $swData['deliveryTime'];
        if (empty($productDeliveryTime)) {
            return $productData;
        }
        unset($swData['deliveryTime']);

        $deliveryTimeId = $this->deliveryTimeProvider->getIdByName($productDeliveryTime, $context);

        $swData['deliveryTimeId'] = $deliveryTimeId;
        $productData->setShopwareData($swData);

        return  $productData;
    }
}

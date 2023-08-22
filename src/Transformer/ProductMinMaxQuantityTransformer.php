<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class ProductMinMaxQuantityTransformer implements ProductDataTransformerInterface
{
    private LoggerInterface $ergonodeSyncLogger;

    public function __construct(
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->ergonodeSyncLogger = $ergonodeSyncLogger;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $shopwareData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();

        $maxPurchase = $ergonodeData->getMaxPurchase();
        $minPurchase = $ergonodeData->getMinPurchase();

        if ($maxPurchase && $minPurchase && $minPurchase > $maxPurchase) {
            return $this->handleMinGteMax($minPurchase, $maxPurchase, $productData);
        }

        if ($ergonodeData->getMinPurchase() === 0) {
            $this->logGteOne(
                $productData->getSku(),
                $productData->getSwProductId(),
                $ergonodeData->getMinPurchase(),
                'minPurchase'
            );
            $shopwareData->setMinPurchase(null);
        } else {
            $shopwareData->setMinPurchase($ergonodeData->getMinPurchase());
        }

        if ($ergonodeData->getMaxPurchase() === 0) {
            $this->logGteOne(
                $productData->getSku(),
                $productData->getSwProductId(),
                $ergonodeData->getMaxPurchase(),
                'maxPurchase'
            );
            $shopwareData->setMaxPurchase(null);
        } else {
            $shopwareData->setMaxPurchase($ergonodeData->getMaxPurchase());
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }

    private function logGteOne(string $sku, string $productId, ?int $value, string $key): void
    {
        $this->ergonodeSyncLogger->warning(
            sprintf('Product %s equals 0, but should be greater or equal 1. Value has been erased.', $key),
            [
                'value' => $value,
                'sku' => $sku,
                'productId' => $productId,
            ]
        );
    }

    private function handleMinGteMax(
        int $minPurchase,
        int $maxPurchase,
        ProductTransformationDTO $productData
    ): ProductTransformationDTO {
        $shopwareData = $productData->getShopwareData();
        $this->ergonodeSyncLogger->warning(
            'Product minPurchase is greater than maxPurchase. Both values have been erased.',
            [
                'minPurchase' => $minPurchase,
                'maxPurchase' => $maxPurchase,
                'sku' => $productData->getSku(),
                'productId' => $productData->getSwProductId(),
            ]
        );

        $shopwareData->setMinPurchase(null);
        $shopwareData->setMaxPurchase(null);

        $productData->setShopwareData($shopwareData);

        return $productData;
    }
}

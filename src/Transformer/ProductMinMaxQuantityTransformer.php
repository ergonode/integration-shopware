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
        $this->ensureMinLteMax($productData);

        $this->ensureGteOne($productData, 'minPurchase');
        $this->ensureGteOne($productData, 'maxPurchase');

        $this->handleErasing($productData, 'minPurchase');
        $this->handleErasing($productData, 'maxPurchase');

        return $productData;
    }

    private function ensureMinLteMax(ProductTransformationDTO $productData): void
    {
        $swData = $productData->getShopwareData();

        if (
            isset($swData['minPurchase'])
            && isset($swData['maxPurchase'])
            && $swData['minPurchase'] > $swData['maxPurchase']
        ) {
            $this->ergonodeSyncLogger->warning(
                'Product minPurchase is greater than maxPurchase. Both values have been erased.',
                [
                    'minPurchase' => $swData['minPurchase'],
                    'maxPurchase' => $swData['maxPurchase'],
                    'sku' => $productData->getSku(),
                    'productId' => $productData->getSwProductId(),
                ]
            );

            $swData['minPurchase'] = null;
            $swData['maxPurchase'] = null;
        }

        $productData->setShopwareData($swData);
    }

    private function ensureGteOne(ProductTransformationDTO $productData, string $key): void
    {
        $swData = $productData->getShopwareData();

        if (isset($swData[$key]) && 0 === $swData[$key]) {
            $this->ergonodeSyncLogger->warning(
                sprintf('Product %s equals 0, but should be greater or equal 1. Value has been erased.', $key),
                [
                    'value' => $swData[$key],
                    'sku' => $productData->getSku(),
                    'productId' => $productData->getSwProductId(),
                ]
            );

            $swData[$key] = null;
        }

        $productData->setShopwareData($swData);
    }

    private function handleErasing(ProductTransformationDTO $productData, string $key): void
    {
        $swData = $productData->getShopwareData();

        if (false === isset($swData[$key])) {
            $swData[$key] = null;
        }

        $productData->setShopwareData($swData);
    }
}
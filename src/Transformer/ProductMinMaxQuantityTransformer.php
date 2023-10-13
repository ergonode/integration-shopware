<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductErgonodeData;
use Ergonode\IntegrationShopware\DTO\ProductShopwareData;
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

        $minPurchase = $this->getMinPurchase($productData);
        $maxPurchase = $this->getMaxPurchase($productData);
        $shopwareData->setMinPurchase($minPurchase);
        $shopwareData->setMaxPurchase($maxPurchase);

        if ($minPurchase && $maxPurchase && $minPurchase > $maxPurchase) {
            $productData = $this->handleMinGteMax($minPurchase, $maxPurchase, $productData);
        }

        $productData->setShopwareData($shopwareData);

        return $productData;
    }

    private function logGteOne(string $sku, ?string $productId, ?int $value, string $key): void
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
                'sku' => $productData->getErgonodeData()->getSku(),
                'productId' => $productData->getSwProductId(),
            ]
        );

        $shopwareData->setMinPurchase(null);
        $shopwareData->setMaxPurchase(null);

        $productData->setShopwareData($shopwareData);

        return $productData;
    }

    private function getMinPurchase(
        ProductTransformationDTO $productData,
    ): ?int {
        $ergonodeData = $productData->getErgonodeData();
        $minPurchaseMapping = $ergonodeData->getMinPurchase();
        $defaultLanguage = $productData->getDefaultLanguage();
        $existingMinPurchase = $productData->getSwProduct()?->getMinPurchase();
        // if unmapped, return current value
        if ($minPurchaseMapping === false) {
            return $existingMinPurchase;
        }

        $minPurchase = $minPurchaseMapping?->getTranslation($defaultLanguage)?->getValue();
        if (is_int($minPurchase)) {
            if ($minPurchase === 0) {
                $this->logGteOne(
                    $productData->getErgonodeData()->getSku(),
                    $productData->getSwProductId(),
                    $minPurchase,
                    'minPurchase'
                );
            } else {
                return $minPurchase;
            }
        }

        return null;
    }

    private function getMaxPurchase(
        ProductTransformationDTO $productData,
    ): ?int {
        $ergonodeData = $productData->getErgonodeData();
        $maxPurchaseMapping = $ergonodeData->getMaxPurchase();
        $defaultLanguage = $productData->getDefaultLanguage();
        $existingMaxPurchase = $productData->getSwProduct()?->getMaxPurchase();
        // if unmapped, return current value
        if ($maxPurchaseMapping === false) {
            return $existingMaxPurchase;
        }

        $maxPurchase = $maxPurchaseMapping?->getTranslation($defaultLanguage)?->getValue();
        if (is_int($maxPurchase)) {
            if ($maxPurchase=== 0) {
                $this->logGteOne(
                    $productData->getErgonodeData()->getSku(),
                    $productData->getSwProductId(),
                    $maxPurchase,
                    'maxPurchase'
                );
            } else {
                return $maxPurchase;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();
        $defaultLanguage = $productData->getDefaultLanguage();

        $pricePayload = [
            'linked' => false,
            'currencyId' => Defaults::CURRENCY,
        ];

        if (!$productData->getSwProduct()?->getPrice()) {
            $pricePayload['gross'] = (float)$ergonodeData->getPriceGross()?->getTranslation($defaultLanguage)?->getValue() ?? 0;
            $pricePayload['net']  = (float)$ergonodeData->getPriceNet()?->getTranslation($defaultLanguage)?->getValue() ?? 0;
        } else {
            $pricePayload['gross'] = $ergonodeData->getPriceGross()
                ? (float)$ergonodeData->getPriceGross()?->getTranslation($defaultLanguage)?->getValue()
                : $this->getExistingGrossPrice($productData);
            $pricePayload['net'] = $ergonodeData->getPriceNet()
                ? (float)$ergonodeData->getPriceNet()?->getTranslation($defaultLanguage)?->getValue()
                : $this->getExistingNetPrice($productData);
        }

        $swData->setPrice([$pricePayload]);

        $productData->setShopwareData($swData);

        return $productData;
    }

    private function getExistingGrossPrice(ProductTransformationDTO $productTransformationDTO): ?float
    {
        $price = $productTransformationDTO->getSwProduct()?->getPrice()->getCurrencyPrice(Defaults::CURRENCY);

        return $price ? $price->getGross() : 0;
    }

    private function getExistingNetPrice(ProductTransformationDTO $productTransformationDTO): ?float
    {
        $price = $productTransformationDTO->getSwProduct()?->getPrice()->getCurrencyPrice(Defaults::CURRENCY);

        return $price ? $price->getNet() : 0;
    }
}

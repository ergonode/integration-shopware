<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    const DEFAULT_GROSS_PRICE = 99999; // devEcommerce change

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();
        $defaultLanguage = $productData->getDefaultLanguage();

        $pricePayload = [
            'linked' => true, // devEcommerce change
            'currencyId' => Defaults::CURRENCY,
        ];

        if (!$productData->getSwProduct()?->getPrice()) {
            $pricePayload['gross'] = self::DEFAULT_GROSS_PRICE; // devEcommerce change
            if ($ergonodeData->getPriceGross() instanceof ProductAttribute) {
                $pricePayload['gross'] = (float)$ergonodeData->getPriceGross()->getTranslation($defaultLanguage)?->getValue() ?? self::DEFAULT_GROSS_PRICE; // devEcommerce change
            }

            $pricePayload['net'] = self::DEFAULT_GROSS_PRICE; // devEcommerce change
            if ($ergonodeData->getPriceNet() instanceof ProductAttribute) {
                $pricePayload['net'] = (float)$ergonodeData->getPriceNet()->getTranslation($defaultLanguage)?->getValue(
                ) ?? self::DEFAULT_GROSS_PRICE; // devEcommerce change
            }
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

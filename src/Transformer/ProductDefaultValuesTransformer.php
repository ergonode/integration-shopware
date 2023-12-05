<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Framework\Context;

class ProductDefaultValuesTransformer implements ProductDataTransformerInterface
{
    private const DEFAULT_STOCK_VALUE = 999;

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();
        $sku = $ergonodeData->getSku();
        $swData->setProductNumber($sku);

        $swData->setName($this->getName($productData));
        $swData->setStock($this->getStock($productData));

        foreach ($ergonodeData->getName()?->getTranslations() ?? [] as $translation) {
            if (!is_null($translation->getValue())) {
                $swData->setTranslatedName(
                    IsoCodeConverter::ergonodeToShopwareIso($translation->getLanguage()),
                    $translation->getValue()
                );
            }
        }
        $productData->setShopwareData($swData);

        return $productData;
    }

    private function getName(ProductTransformationDTO $productData): string
    {
        $ergonodeData = $productData->getErgonodeData();
        $name = $ergonodeData->getName();
        if ($name instanceof ProductAttribute) {
            $defaultLanguage = $productData->getDefaultLanguage();
            $translation = $name->getTranslation($defaultLanguage);
            if ($translation && !is_null($translation->getValue())) {
                return $translation->getValue();
            }
        }

        if ($productData->getSwProduct()?->getName()) {
            return $productData->getSwProduct()?->getName();
        }

        return $productData->getErgonodeData()->getSku();
    }

    private function getStock(ProductTransformationDTO $productData): int
    {
        $ergonodeData = $productData->getErgonodeData();
        $stock = $ergonodeData->getStock();
        if ($stock instanceof ProductAttribute) {
            $defaultLanguage = $productData->getDefaultLanguage();
            $translation = $stock->getTranslation($defaultLanguage);
            if ($translation && !is_null($translation->getValue())) {
                return $translation->getValue();
            }
        }

        if ($productData->getSwProduct()?->getStock()) {
            return $productData->getSwProduct()?->getStock();
        }

        return self::DEFAULT_STOCK_VALUE;
    }
}

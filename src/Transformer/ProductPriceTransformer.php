<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

use function array_merge;

class ProductPriceTransformer implements ProductDataTransformerInterface
{
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();
        if(!$ergonodeData->getPriceNet() && !$ergonodeData->getPriceGross()) {
            return $productData;
        }

        foreach($existingPrice->getElements() as $element) {
            $elementArray = (array)$element;
            var_dump(array_keys($elementArray));
            //foreach($element as $row) {
            //
            //    var_dump(((array)$row)['currencyId']);
            //}
        }
        //dump($existingPrice);
        throw new \Exception('rpcei');
        $price = $swData->getPrice();
        $price = [
            array_merge(
                [
                    'gross' => 0,
                    'net' => 0,
                    'linked' => false,
                    'currencyId' => Defaults::CURRENCY,
                ],
                $price
            ),
        ];
        $swData->setPrice($price);
        $productData->setShopwareData($swData);

        return $productData;
    }
}

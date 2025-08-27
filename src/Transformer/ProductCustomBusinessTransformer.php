<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class ProductCustomBusinessTransformer implements ProductDataTransformerInterface {
    private ConfigService $configService;

    public function __construct(ConfigService $configService) {
        $this->configService = $configService;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO {
        $swData = $productData->getShopwareData();
        $ergonodeData = $productData->getErgonodeData();
        $sku = $ergonodeData->getSku();

        if (preg_match('/^V[0-9]+_.+$/', $sku)) { // handle for good parts
            // inherit from a parent
            $swData->setName('');
            $swData->setTax(null);
            $swData->setPrice(null);
            $swData->setData('isCloseout', null);
            $swData->setData('shippingFree', null);
            $swData->setData('markAsTopseller', null);
            $swData->setData('minPurchase', null);
            $swData->setData('purchaseSteps', null);
            $swData->setData('active', null);
            $swData->setProperties([]);
        }

        $productData->setShopwareData($swData);

        return $productData;
    }

}

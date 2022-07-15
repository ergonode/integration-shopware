<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\TaxProvider;
use RuntimeException;
use Shopware\Core\Framework\Context;

class ProductTaxTransformer implements ProductDataTransformerInterface
{
    private TaxProvider $taxProvider;

    public function __construct(TaxProvider $taxProvider)
    {
        $this->taxProvider = $taxProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        if ($productData->isUpdate()) {
            return $productData;
        }

        $defaultTax = $this->taxProvider->getDefaultTax($context);
        if (null === $defaultTax) {
            throw new RuntimeException('Could not load default tax entity');
        }

        $swData = $productData->getShopwareData();
        $swData['taxId'] = $defaultTax->getId();
        $productData->setShopwareData($swData);

        return $productData;
    }
}
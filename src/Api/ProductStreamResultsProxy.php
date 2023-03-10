<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ProductStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'productStream';
    public const VARIANT_FIELD_PATTERN = 'variant_%s';

    public function getProductData(): array
    {
        return $this->getMainData();
    }
}

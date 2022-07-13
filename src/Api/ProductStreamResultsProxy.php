<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ProductStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'productStream';

    public function getProductData(): array
    {
        return $this->getMainData();
    }
}
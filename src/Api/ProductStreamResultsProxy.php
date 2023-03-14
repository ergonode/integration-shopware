<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ProductStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'productStream';
    public const VARIANT_LIST_FIELD = 'variantList';
    public const CATEGORY_LIST_FIELD = 'categoryList';

    public function getProductData(): array
    {
        return $this->getMainData();
    }
}

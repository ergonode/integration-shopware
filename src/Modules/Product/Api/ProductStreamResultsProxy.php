<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Api;

use Strix\Ergonode\Api\AbstractStreamResultsProxy;

class ProductStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'productStream';

    public function getProductData(): array
    {
        return $this->getMainData();
    }
}
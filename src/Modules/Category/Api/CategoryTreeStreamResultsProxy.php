<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Modules\Category\Api;

use Ergonode\IntegrationShopware\Api\AbstractStreamResultsProxy;

class CategoryTreeStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'categoryTreeStream';
}
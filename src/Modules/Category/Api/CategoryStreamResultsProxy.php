<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Category\Api;

use Strix\Ergonode\Api\AbstractStreamResultsProxy;

class CategoryStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'categoryStream';
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Api;

use Strix\Ergonode\Api\AbstractStreamResultsProxy;

class AttributeDeletedStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'attributeDeletedStream';
}
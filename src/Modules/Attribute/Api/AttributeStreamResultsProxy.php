<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Api;

use Strix\Ergonode\Api\AbstractStreamResultsProxy;

class AttributeStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'attributeStream';

    /**
     * @param string[] $array
     */
    public function filterByAttributeTypes(array $array = []): AbstractStreamResultsProxy
    {
        return $this->filter(
            fn(array $attribute) => array_intersect($array, array_keys($attribute['node'] ?? []))
        );
    }
}
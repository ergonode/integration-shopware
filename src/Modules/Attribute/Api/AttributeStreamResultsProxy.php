<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Api;

use Strix\Ergonode\Api\AbstractStreamResultsProxy;

class AttributeStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'attributeStream';

    public function filterByAttributeTypes(array $array = []): self
    {
        $filteredResults = clone $this;

        $filteredEdges = array_filter(
            $filteredResults->getEdges(),
            fn(array $attribute) => array_intersect($array, array_keys($attribute['node'] ?? []))
        );

        $filteredResults->results['data'][static::MAIN_FIELD]['edges'] = $filteredEdges;
        $filteredResults->results['data'][static::MAIN_FIELD]['totalCount'] = count($filteredEdges);

        return $filteredResults;
    }
}
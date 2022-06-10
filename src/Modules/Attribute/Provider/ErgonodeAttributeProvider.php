<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Provider;

use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Enum\AttributeTypes;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;

class ErgonodeAttributeProvider
{
    private const MAX_ATTRIBUTES_PER_PAGE = 200;

    private AttributeQueryBuilder $attributeQueryBuilder;

    private CachedErgonodeGqlClient $ergonodeGqlClient;

    public function __construct(
        AttributeQueryBuilder $attributeQueryBuilder,
        CachedErgonodeGqlClient $ergonodeGqlClient
    ) {
        $this->attributeQueryBuilder = $attributeQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideProductAttributes(): array
    {
        $endCursor = null;
        $attributes = [];

        do {
            $query = $this->attributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeStreamResultsProxy::class);

            if (!$results instanceof AttributeStreamResultsProxy) {
                continue;
            }

            $attributes = array_merge($attributes, $results->getEdges());
            $endCursor = $results->getEndCursor();
        } while ($results->hasNextPage());

        return $attributes;
    }

    public function provideBindingAttributes(): ?AttributeStreamResultsProxy
    {
        $endCursor = null;
        $attributes = null;

        do {
            $query = $this->attributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeStreamResultsProxy::class);

            if (!$results instanceof AttributeStreamResultsProxy) {
                return null;
            }

            $endCursor = $results->getEndCursor();
            $filteredResults = $results->filterByAttributeTypes([
                AttributeTypes::SELECT,
                AttributeTypes::MULTISELECT,
            ]);

            if (isset($attributes) && $attributes instanceof AttributeStreamResultsProxy) {
                $attributes->merge($filteredResults);

                continue;
            }

            $attributes = $filteredResults;
        } while ($results->hasNextPage());

        return $attributes;
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Provider;

use Generator;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Modules\Attribute\Api\AttributeDeletedStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\Api\AttributeStreamResultsProxy;
use Strix\Ergonode\Modules\Attribute\QueryBuilder\AttributeQueryBuilder;

class ErgonodeAttributeProvider
{
    private const MAX_ATTRIBUTES_PER_PAGE = 200;

    private AttributeQueryBuilder $attributeQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    public function __construct(
        AttributeQueryBuilder $attributeQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
        $this->attributeQueryBuilder = $attributeQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provideProductAttributes(?string $endCursor = null): Generator
    {
        do {
            $query = $this->attributeQueryBuilder->build(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeStreamResultsProxy::class);

            if (!$results instanceof AttributeStreamResultsProxy) {
                continue;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results->hasNextPage());
    }

    public function provideDeletedAttributes(?string $endCursor = null): Generator
    {
        do {
            $query = $this->attributeQueryBuilder->buildDeleted(self::MAX_ATTRIBUTES_PER_PAGE, $endCursor);
            $results = $this->ergonodeGqlClient->query($query, AttributeDeletedStreamResultsProxy::class);

            if (!$results instanceof AttributeDeletedStreamResultsProxy) {
                continue;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results->hasNextPage());
    }
}
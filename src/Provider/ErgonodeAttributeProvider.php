<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\AttributeDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\AttributeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\QueryBuilder\AttributeQueryBuilder;
use Generator;

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
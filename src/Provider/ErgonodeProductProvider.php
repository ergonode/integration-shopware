<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\QueryBuilder\ProductQueryBuilder;
use Generator;

class ErgonodeProductProvider
{
    private ProductQueryBuilder $productQueryBuilder;

    private ErgonodeGqlClientInterface $ergonodeGqlClient;

    public function __construct(
        ProductQueryBuilder $productQueryBuilder,
        ErgonodeGqlClientInterface $ergonodeGqlClient
    ) {
        $this->productQueryBuilder = $productQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provide(int $count, ?string $cursor = null): ?ProductStreamResultsProxy
    {
        $query = $this->productQueryBuilder->build($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query, ProductStreamResultsProxy::class);

        if (!$response instanceof ProductStreamResultsProxy) {
            return null;
        }

        return $response;
    }

    public function provideDeleted(?int $count = null, ?string $cursor = null): ?ProductStreamResultsProxy
    {
        $query = $this->productQueryBuilder->buildDeleted($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query, ProductStreamResultsProxy::class);

        if (!$response instanceof ProductStreamResultsProxy) {
            return null;
        }

        return $response;
    }

    public function provideOnlySkus(int $count, ?string $endCursor = null, ?ErgonodeGqlClient $client = null): Generator
    {
        if (null === $client) {
            $client = $this->ergonodeGqlClient;
        }

        do {
            $query = $this->productQueryBuilder->buildOnlySkus($count, $endCursor);
            $results = $client->query($query, ProductStreamResultsProxy::class);

            if (!$results instanceof ProductStreamResultsProxy) {
                return null;
            }

            yield $results;

            $endCursor = $results->getEndCursor();
        } while ($results->hasNextPage());
    }
}
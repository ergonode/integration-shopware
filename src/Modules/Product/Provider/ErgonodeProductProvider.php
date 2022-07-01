<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Provider;

use Generator;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Modules\Product\Api\ProductStreamResultsProxy;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;

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
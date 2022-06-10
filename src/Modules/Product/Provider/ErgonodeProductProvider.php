<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Provider;

use Strix\Ergonode\Api\Client\CachedErgonodeGqlClient;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;
use Strix\Ergonode\Modules\Product\Api\ProductStreamResultsProxy;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;

class ErgonodeProductProvider
{
    private ProductQueryBuilder $productQueryBuilder;

    private CachedErgonodeGqlClient $ergonodeGqlClient;

    public function __construct(
        ProductQueryBuilder $productQueryBuilder,
        CachedErgonodeGqlClient $ergonodeGqlClient
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

    public function provideProductWithVariants(string $sku): ?ProductResultsProxy
    {
        $query = $this->productQueryBuilder->buildProductWithVariants($sku);
        $response = $this->ergonodeGqlClient->query($query, ProductResultsProxy::class);

        if (!$response instanceof ProductResultsProxy) {
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
}
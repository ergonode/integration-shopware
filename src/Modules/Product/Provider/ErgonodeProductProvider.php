<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Provider;

use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
use Strix\Ergonode\Modules\Product\Struct\ErgonodeDeletedProduct;
use Strix\Ergonode\Modules\Product\Struct\ErgonodeProduct;
use Strix\Ergonode\Struct\ErgonodeEntityStreamCollection;
use Strix\Ergonode\Transformer\StreamResponseTransformer;

class ErgonodeProductProvider
{
    private ProductQueryBuilder $productQueryBuilder;

    private ErgonodeGqlClient $ergonodeGqlClient;

    private StreamResponseTransformer $streamResponseTransformer;

    public function __construct(
        ProductQueryBuilder $productQueryBuilder,
        ErgonodeGqlClient $ergonodeGqlClient,
        StreamResponseTransformer $streamResponseTransformer
    ) {
        $this->productQueryBuilder = $productQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
        $this->streamResponseTransformer = $streamResponseTransformer;
    }

    public function provide(int $count, ?string $cursor = null): ?ErgonodeEntityStreamCollection
    {
        $query = $this->productQueryBuilder->build($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return $this->streamResponseTransformer->transformResponse(
            ErgonodeProduct::class,
            $response->getData()['productStream'] ?? []
        );
    }

    public function provideSingleProduct(string $sku): ?ErgonodeEntityStreamCollection
    {
        $query = $this->productQueryBuilder->buildSingleProduct($sku);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return $this->streamResponseTransformer->transformResponse(
            ErgonodeProduct::class,
            $response->getData() ?? []
        );
    }

    public function provideDeleted(?int $count = null, ?string $cursor = null): ?ErgonodeEntityStreamCollection
    {
        $query = $this->productQueryBuilder->buildDeleted($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return $this->streamResponseTransformer->transformResponse(
            ErgonodeDeletedProduct::class,
            $response->getData()['productDeletedStream'] ?? []
        );
    }
}
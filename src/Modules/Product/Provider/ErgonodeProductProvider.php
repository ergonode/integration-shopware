<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Provider;

use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
use Strix\Ergonode\Modules\Product\Struct\ErgonodeDeletedProduct;
use Strix\Ergonode\Modules\Product\Struct\ErgonodeProduct;
use Strix\Ergonode\Struct\StreamResult;

class ErgonodeProductProvider
{
    private ProductQueryBuilder $productQueryBuilder;

    private ErgonodeGqlClient $ergonodeGqlClient;

    public function __construct(
        ProductQueryBuilder $productQueryBuilder,
        ErgonodeGqlClient $ergonodeGqlClient
    ) {
        $this->productQueryBuilder = $productQueryBuilder;
        $this->ergonodeGqlClient = $ergonodeGqlClient;
    }

    public function provide(int $count, ?string $cursor = null): ?StreamResult
    {
        $query = $this->productQueryBuilder->build($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return new StreamResult(
            ErgonodeProduct::class,
            $response->getData()['productStream'] ?? []
        );
    }

    public function provideSingleProduct(string $sku): ?StreamResult
    {
        $query = $this->productQueryBuilder->buildSingleProduct($sku);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return new StreamResult(
            ErgonodeProduct::class,
            $response->getData() ?? []
        );
    }

    public function provideDeleted(?int $count = null, ?string $cursor = null): ?StreamResult
    {
        $query = $this->productQueryBuilder->buildDeleted($count, $cursor);
        $response = $this->ergonodeGqlClient->query($query);

        if (false === $response->isOk()) {
            return null;
        }

        return new StreamResult(
            ErgonodeDeletedProduct::class,
            $response->getData()['productDeletedStream'] ?? []
        );
    }
}
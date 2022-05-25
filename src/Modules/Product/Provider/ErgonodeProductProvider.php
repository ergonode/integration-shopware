<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Provider;

use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
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

        if ($response->isOk()) {
            return new StreamResult(
                ErgonodeProduct::class,
                $response->getData()['productStream']
            );
        }

        return null;
    }

    public function provideSingleProduct(string $sku): ?StreamResult
    {
        $query = $this->productQueryBuilder->buildSingleProduct($sku);
        $response = $this->ergonodeGqlClient->query($query);

        if ($response->isOk()) {
            return new StreamResult(
                ErgonodeProduct::class,
                $response->getData()
            );
        }

        return null;
    }

    public function provideDeleted(?string $cursor = null): ?StreamResult
    {
        $query = $this->productQueryBuilder->buildDeleted(null, $cursor);
        $response = $this->ergonodeGqlClient->query($query);

        if ($response->isOk()) {
            return new StreamResult(
                ErgonodeProduct::class,
                $response->getData()['productDeletedStream']
            );
        }

        return null;
    }
}
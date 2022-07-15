<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use GraphQL\Query;
use GraphQL\Results;
use Symfony\Contracts\Cache\CacheInterface;

class CachedErgonodeGqlClient implements ErgonodeGqlClientInterface
{
    private ErgonodeGqlClient $ergonodeGqlClient;

    private CacheInterface $cache;

    public function __construct(
        ErgonodeGqlClient $ergonodeGqlClient,
        CacheInterface $gqlRequestCache
    ) {
        $this->ergonodeGqlClient = $ergonodeGqlClient;
        $this->cache = $gqlRequestCache;
    }

    public function query(Query $query, ?string $proxyClass = null): ?Results
    {
        $queryHash = \sprintf('%s_%s', \md5(\strval($query)), $proxyClass);

        return $this->cache->get($queryHash, fn() => $this->ergonodeGqlClient->query($query, $proxyClass));
    }
}
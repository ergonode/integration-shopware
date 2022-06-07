<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Query;
use Strix\Ergonode\Api\GqlResponse;
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

    public function query(Query $query): ?GqlResponse
    {
        $queryHash = \md5(\strval($query));

        return $this->cache->get($queryHash, fn() => $this->ergonodeGqlClient->query($query));
    }
}
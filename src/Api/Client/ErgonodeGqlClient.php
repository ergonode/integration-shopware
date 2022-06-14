<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Client;
use GraphQL\Query;
use GraphQL\Results;
use GuzzleHttp\Exception\ClientException;

class ErgonodeGqlClient implements ErgonodeGqlClientInterface
{
    private Client $gqlClient;

    public function __construct(
        Client $gqlClient
    ) {
        $this->gqlClient = $gqlClient;
    }

    public function query(Query $query, ?string $proxyClass = null): ?Results
    {
        try {
            $results = $this->gqlClient->runQuery($query, true);

            if (null !== $proxyClass && in_array(Results::class, class_parents($proxyClass))) {
                return new $proxyClass($results);
            }

            return $results;
        } catch (ClientException $e) {
            // TODO log
            dump($e);
        }

        return null;
    }
}
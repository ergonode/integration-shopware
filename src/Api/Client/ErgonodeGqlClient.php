<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use GraphQL\Client;
use GraphQL\Query;
use GraphQL\Results;
use Psr\Http\Client\ClientExceptionInterface;

class ErgonodeGqlClient implements ErgonodeGqlClientInterface
{
    private Client $gqlClient;

    private ?string $salesChannelId;

    public function __construct(
        Client $gqlClient,
        ?string $salesChannelId = null
    ) {
        $this->gqlClient = $gqlClient;
        $this->salesChannelId = $salesChannelId;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function query(Query $query, ?string $proxyClass = null): ?Results
    {
        try {
            $results = $this->gqlClient->runQuery($query, true);

            if (null !== $proxyClass && in_array(Results::class, class_parents($proxyClass))) {
                return new $proxyClass($results);
            }

            return $results;
        } catch (ClientExceptionInterface $e) {
            // TODO log
            dump($e);
        }

        return null;
    }
}
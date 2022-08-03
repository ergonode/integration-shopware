<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Results;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

class ErgonodeGqlClient implements ErgonodeGqlClientInterface
{
    private const MAX_LOGGED_QUERY_CHARS = 300;

    private Client $gqlClient;

    private LoggerInterface $apiLogger;

    private ?string $salesChannelId;

    public function __construct(
        Client $gqlClient,
        LoggerInterface $ergonodeApiLogger,
        ?string $salesChannelId = null
    ) {
        $this->gqlClient = $gqlClient;
        $this->apiLogger = $ergonodeApiLogger;
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
        } catch (ClientExceptionInterface|QueryError $e) {
            $queryStr = trim((string)$query);
            if (strlen($queryStr) > self::MAX_LOGGED_QUERY_CHARS) {
                $queryStr = sprintf(
                    '%s... (query shortened to save space)',
                    substr($queryStr, 0, self::MAX_LOGGED_QUERY_CHARS),
                );
            }

            $this->apiLogger->error('Failed to execute GraphQL query', [
                'message' => $e->getMessage(),
                'query' => $queryStr,
            ]);
        }

        return null;
    }
}
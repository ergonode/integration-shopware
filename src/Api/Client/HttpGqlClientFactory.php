<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Client;
use Strix\Ergonode\Api\ErgonodeAccessData;

class HttpGqlClientFactory
{
    private const GRAPHQL_ENDPOINT = 'api/graphql/';

    private const TIMEOUT_SEC = 10;

    public function create(ErgonodeAccessData $accessData): Client
    {
        return new Client(
            self::GRAPHQL_ENDPOINT,
            [
                'X-API-KEY' => $accessData->getApiKey(),
            ],
            [
                'base_uri' => $accessData->getBaseUrl(),
                'timeout' => self::TIMEOUT_SEC,
            ]
        );
    }
}
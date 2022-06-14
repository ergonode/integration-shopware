<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GraphQL\Client;
use Strix\Ergonode\Api\ErgonodeAccessData;

class HttpGqlClientFactory
{
    private const GRAPHQL_ENDPOINT = 'api/graphql/';

    public function create(ErgonodeAccessData $accessData): Client
    {
        return new Client(
            self::GRAPHQL_ENDPOINT,
            [
                'X-API-KEY' => $accessData->getApiKey(),
            ],
            [
                'base_uri' => $accessData->getBaseUrl(),
            ]
        );
    }
}
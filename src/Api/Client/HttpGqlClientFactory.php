<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use GraphQL\Client;

class HttpGqlClientFactory
{
    private const GRAPHQL_ENDPOINT = 'api/graphql/';

    private const TIMEOUT_SEC = 30;

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
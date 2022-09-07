<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use GraphQL\Client;

class HttpGqlClientFactory
{
    private const TIMEOUT_SEC = 60;

    public function create(ErgonodeAccessData $accessData): Client
    {
        return new Client(
            $accessData->getApiEndpoint(),
            [
                'X-API-KEY' => $accessData->getApiKey(),
            ],
            [
                'timeout' => self::TIMEOUT_SEC,
            ]
        );
    }
}
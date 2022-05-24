<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use GuzzleHttp\Client;

class HttpGqlClientFactory
{
    public function create(ErgonodeAccessData $accessData): Client
    {
        return new Client([
            'base_uri' => $accessData->getBaseUrl(),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY' => $accessData->getApiKey(),
            ],
        ]);
    }
}
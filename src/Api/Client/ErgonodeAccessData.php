<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

class ErgonodeAccessData
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
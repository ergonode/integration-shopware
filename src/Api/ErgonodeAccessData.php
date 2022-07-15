<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ErgonodeAccessData
{
    private string $baseUrl;

    private string $apiKey;

    private ?string $salesChannelId;

    public function __construct(string $baseUrl, string $apiKey, ?string $salesChannelId = null)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->salesChannelId = $salesChannelId;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }
}
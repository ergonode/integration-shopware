<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

class ErgonodeAccessData
{
    private string $apiEndpoint;

    private string $apiKey;

    private ?string $salesChannelId;

    public function __construct(string $apiEndpoint, string $apiKey, ?string $salesChannelId = null)
    {
        $this->apiEndpoint = $apiEndpoint;
        $this->apiKey = $apiKey;
        $this->salesChannelId = $salesChannelId;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
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
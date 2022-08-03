<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Generator;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class ErgonodeGqlClientFactory
{
    private ConfigProvider $configProvider;

    private HttpGqlClientFactory $httpClientFactory;

    private LoggerInterface $apiLogger;

    public function __construct(
        ConfigProvider $configProvider,
        HttpGqlClientFactory $httpClientFactory,
        LoggerInterface $ergonodeApiLogger
    ) {
        $this->configProvider = $configProvider;
        $this->httpClientFactory = $httpClientFactory;
        $this->apiLogger = $ergonodeApiLogger;
    }

    public function createFromPluginConfig(): ErgonodeGqlClient
    {
        return $this->create(
            $this->configProvider->getErgonodeAccessData()
        );
    }

    public function createForEverySalesChannel(Context $context): Generator
    {
        $accessDataArray = $this->configProvider->getSalesChannelErgonodeAccessData($context);

        foreach ($accessDataArray as $accessData) {
            yield $this->create($accessData);
        }
    }

    public function create(ErgonodeAccessData $accessData): ErgonodeGqlClient
    {
        return new ErgonodeGqlClient(
            $this->httpClientFactory->create($accessData),
            $this->apiLogger,
            $accessData->getSalesChannelId()
        );
    }
}

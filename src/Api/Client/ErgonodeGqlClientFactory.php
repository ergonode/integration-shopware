<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api\Client;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Generator;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class ErgonodeGqlClientFactory
{
    private ConfigService $configService;

    private HttpGqlClientFactory $httpClientFactory;

    private LoggerInterface $apiLogger;

    public function __construct(
        ConfigService $configService,
        HttpGqlClientFactory $httpClientFactory,
        LoggerInterface $ergonodeApiLogger
    ) {
        $this->configService = $configService;
        $this->httpClientFactory = $httpClientFactory;
        $this->apiLogger = $ergonodeApiLogger;
    }

    public function createFromPluginConfig(): ErgonodeGqlClient
    {
        return $this->create(
            $this->configService->getErgonodeAccessData()
        );
    }

    public function createForEverySalesChannel(Context $context): Generator
    {
        $accessDataArray = $this->configService->getSalesChannelErgonodeAccessData($context);

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

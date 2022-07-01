<?php

declare(strict_types=1);

namespace Strix\Ergonode\Api\Client;

use Generator;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\ErgonodeAccessData;
use Strix\Ergonode\Provider\ConfigProvider;

class ErgonodeGqlClientFactory
{
    private ConfigProvider $configProvider;

    private HttpGqlClientFactory $httpClientFactory;

    public function __construct(
        ConfigProvider $configProvider,
        HttpGqlClientFactory $httpClientFactory
    ) {
        $this->configProvider = $configProvider;
        $this->httpClientFactory = $httpClientFactory;
    }

    public function createFromPluginConfig(): ErgonodeGqlClient
    {
        return $this->create(
            $this->configProvider->getErgonodeAccessData()
        );
    }

    public function createForEverySalesChannel(Context $context): Generator
    {
        $accessDataArray = $this->configProvider->getAllErgonodeAccessData($context);

        foreach ($accessDataArray as $accessData) {
            yield $this->create($accessData);
        }
    }

    public function create(ErgonodeAccessData $accessData): ErgonodeGqlClient
    {
        return new ErgonodeGqlClient(
            $this->httpClientFactory->create($accessData),
            $accessData->getSalesChannelId()
        );
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Api\Client;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\Api\Client\HttpGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ErgonodeGqlClientFactoryTest extends TestCase
{
    /**
     * @var MockObject|ConfigProvider
     */
    private $configProviderMock;

    /**
     * @var MockObject|HttpGqlClientFactory
     */
    private $httpGqlClientFactoryMock;

    /**
     * @var MockObject|ErgonodeAccessData
     */
    private $accessDataMock;

    private ErgonodeGqlClientFactory $gqlClientFactory;

    protected function setUp(): void
    {
        $this->accessDataMock = $this->createMock(ErgonodeAccessData::class);
        $this->configProviderMock = $this->getConfigProviderMock();
        $this->httpGqlClientFactoryMock = $this->createMock(HttpGqlClientFactory::class);

        $this->gqlClientFactory = new ErgonodeGqlClientFactory(
            $this->configProviderMock,
            $this->httpGqlClientFactoryMock
        );
    }

    public function testCreateFromPluginConfigMethod()
    {
        $this->httpGqlClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->accessDataMock);

        $client = $this->gqlClientFactory->createFromPluginConfig();

        $this->assertInstanceOf(ErgonodeGqlClient::class, $client);
    }

    public function testCreateMethod()
    {
        $this->httpGqlClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->accessDataMock);

        $client = $this->gqlClientFactory->create($this->accessDataMock);

        $this->assertInstanceOf(ErgonodeGqlClient::class, $client);
    }

    /**
     * @return MockObject|ConfigProvider
     */
    private function getConfigProviderMock(): MockObject
    {
        return $this->createConfiguredMock(ConfigProvider::class, [
            'getErgonodeAccessData' => $this->accessDataMock,
        ]);
    }
}

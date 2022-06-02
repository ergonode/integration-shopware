<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Api\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientFactory;
use Strix\Ergonode\Api\Client\HttpGqlClientFactory;
use Strix\Ergonode\Api\ErgonodeAccessData;
use Strix\Ergonode\Provider\ConfigProvider;

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

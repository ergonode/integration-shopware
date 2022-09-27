<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Api\Client;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClient;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientFactory;
use Ergonode\IntegrationShopware\Api\Client\HttpGqlClientFactory;
use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\Service\ConfigService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class ErgonodeGqlClientFactoryTest extends TestCase
{
    /**
     * @var MockObject|ConfigService
     */
    private $configServiceMock;

    /**
     * @var MockObject|HttpGqlClientFactory
     */
    private $httpGqlClientFactoryMock;

    /**
     * @var MockObject|ErgonodeAccessData
     */
    private $accessDataMock;

    private ErgonodeGqlClientFactory $gqlClientFactory;

    private LoggerInterface $testLogger;

    protected function setUp(): void
    {
        $this->accessDataMock = $this->createMock(ErgonodeAccessData::class);
        $this->configServiceMock = $this->getConfigServiceMock();
        $this->httpGqlClientFactoryMock = $this->createMock(HttpGqlClientFactory::class);
        $this->testLogger = new TestLogger();

        $this->gqlClientFactory = new ErgonodeGqlClientFactory(
            $this->configServiceMock,
            $this->httpGqlClientFactoryMock,
            $this->testLogger
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
     * @return MockObject|ConfigService
     */
    private function getConfigServiceMock(): MockObject
    {
        return $this->createConfiguredMock(ConfigService::class, [
            'getErgonodeAccessData' => $this->accessDataMock,
        ]);
    }
}

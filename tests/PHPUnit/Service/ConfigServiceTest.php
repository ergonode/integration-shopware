<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\PHPUnit\Service;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigServiceTest extends TestCase
{
    private ConfigService $configService;

    /**
     * @var MockObject|SystemConfigService
     */
    private $systemConfigServiceMock;

    /**
     * @var MockObject|EntityRepository
     */
    private $salesChannelRepositoryMock;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->systemConfigServiceMock = $this->createMock(SystemConfigService::class);
        $this->salesChannelRepositoryMock = $this->createMock(EntityRepository::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->configService = new ConfigService(
            $this->systemConfigServiceMock,
            $this->salesChannelRepositoryMock
        );
    }

    /**
     * @dataProvider salesChannelAccessDataProvider
     */
    public function testGetSalesChannelErgonodeAccessDataGetter(array $mockReturn, array $expectedOutput): void
    {
        $this->mockSystemConfigServiceReturnsAccessData();

        $this->salesChannelRepositoryMock
            ->expects($this->once())
            ->method('search')
            ->willReturn($this->createConfiguredMock(EntitySearchResult::class, [
                'getEntities' => new SalesChannelCollection($mockReturn),
            ]));

        $output = $this->configService->getSalesChannelErgonodeAccessData($this->contextMock);

        $this->assertNotEmpty($output);

        /** @var ErgonodeAccessData $accessData */
        foreach (array_values($output) as $index => $accessData) {
            $this->assertSame(
                $expectedOutput[$index],
                [
                    $accessData->getApiEndpoint(),
                    $accessData->getSalesChannelId(),
                    $accessData->getApiKey(),
                ]
            );
        }
    }

    /**
     * @dataProvider salesChannelAccessDataProvider
     */
    public function testGetSalesChannelErgonodeAccessDataGetterOnEmptyReturn(): void
    {
        $this->mockSystemConfigServiceReturnsAccessData();

        $this->salesChannelRepositoryMock
            ->expects($this->once())
            ->method('search')
            ->willReturn($this->createConfiguredMock(EntitySearchResult::class, [
                'getEntities' => new SalesChannelCollection([]),
            ]));

        $output = $this->configService->getSalesChannelErgonodeAccessData($this->contextMock);

        $this->assertSame([], $output);
    }

    public function testGetErgonodeAccessDataGetter(): void
    {
        $this->mockSystemConfigServiceReturnsAccessData();

        $output = $this->configService->getErgonodeAccessData();

        $this->assertInstanceOf(ErgonodeAccessData::class, $output);
        $this->assertEquals('some_api_endpoint', $output->getApiEndpoint());
        $this->assertEquals('some_api_key', $output->getApiKey());
    }

    /**
     * @dataProvider customFieldKeysDataProvider
     */
    public function testGetErgonodeCustomFieldsGetter($mockReturn, $expectedOutput): void
    {
        $this->systemConfigServiceMock->expects($this->once())
            ->method('get')
            ->with('ErgonodeIntegrationShopware.config.customFieldKeys')
            ->willReturn($mockReturn);

        $output = $this->configService->getErgonodeCustomFieldKeys();

        $this->assertSame($expectedOutput, $output);
    }

    public function customFieldKeysDataProvider(): Generator
    {
        yield [['a', 'b', 'c'], ['a', 'b', 'c']];
        yield ['blabla', []];
    }

    public function salesChannelAccessDataProvider(): Generator
    {
        yield [
            [
                $this->createConfiguredMock(SalesChannelEntity::class, [
                    'getId' => 'some_channel_id',
                ]),
            ],
            [
                [
                    'some_api_endpoint',
                    'some_channel_id',
                    'some_api_key',
                ],
            ],
        ];
    }

    private function mockSystemConfigServiceReturnsAccessData(): void
    {
        $this->systemConfigServiceMock
            ->method('getString')
            ->withConsecutive(
                ['ErgonodeIntegrationShopware.config.ergonodeApiEndpoint'],
                ['ErgonodeIntegrationShopware.config.ergonodeApiKey'],
            )
            ->willReturnOnConsecutiveCalls(
                'some_api_endpoint',
                'some_api_key'
            );
    }
}

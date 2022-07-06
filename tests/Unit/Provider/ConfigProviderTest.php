<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Provider;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigCollection;
use Shopware\Core\System\SystemConfig\SystemConfigEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Strix\Ergonode\Api\ErgonodeAccessData;
use Strix\Ergonode\Provider\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    /**
     * @var MockObject|SystemConfigService
     */
    private $systemConfigServiceMock;

    /**
     * @var MockObject|EntityRepositoryInterface
     */
    private $systemConfigRepositoryMock;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    protected function setUp(): void
    {
        $this->systemConfigServiceMock = $this->createMock(SystemConfigService::class);
        $this->systemConfigRepositoryMock = $this->createMock(EntityRepositoryInterface::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->configProvider = new ConfigProvider(
            $this->systemConfigServiceMock,
            $this->systemConfigRepositoryMock
        );
    }

    /**
     * @dataProvider salesChannelAccessDataProvider
     */
    public function testGetSalesChannelErgonodeAccessDataGetter(array $mockReturn, array $expectedOutput): void
    {
        $this->mockSystemConfigServiceReturnsAccessData();

        $this->systemConfigRepositoryMock
            ->expects($this->once())
            ->method('search')
            ->willReturn($this->createConfiguredMock(EntitySearchResult::class, [
                'getEntities' => new SystemConfigCollection($mockReturn),
            ]));

        $output = $this->configProvider->getSalesChannelErgonodeAccessData($this->contextMock);

        $this->assertNotEmpty($output);

        /** @var ErgonodeAccessData $accessData */
        foreach (array_values($output) as $index => $accessData) {
            $this->assertSame(
                $expectedOutput[$index],
                [
                    $accessData->getBaseUrl(),
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

        $this->systemConfigRepositoryMock
            ->expects($this->once())
            ->method('search')
            ->willReturn($this->createConfiguredMock(EntitySearchResult::class, [
                'getEntities' => new SystemConfigCollection([]),
            ]));

        $output = $this->configProvider->getSalesChannelErgonodeAccessData($this->contextMock);

        $this->assertSame([], $output);
    }

    public function testGetErgonodeAccessDataGetter(): void
    {
        $this->mockSystemConfigServiceReturnsAccessData();

        $output = $this->configProvider->getErgonodeAccessData();

        $this->assertInstanceOf(ErgonodeAccessData::class, $output);
        $this->assertEquals('some_base_url', $output->getBaseUrl());
        $this->assertEquals('some_api_key', $output->getApiKey());
    }

    /**
     * @dataProvider customFieldKeysDataProvider
     */
    public function testGetErgonodeCustomFieldsGetter($mockReturn, $expectedOutput): void
    {
        $this->systemConfigServiceMock->expects($this->once())
            ->method('get')
            ->with('StrixErgonode.config.customFieldKeys')
            ->willReturn($mockReturn);

        $output = $this->configProvider->getErgonodeCustomFields();

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
                $this->mockSystemConfigEntity('sales_channel_1', 'api_key_1'),
            ],
            [
                [
                    'some_base_url',
                    'sales_channel_1',
                    'api_key_1',
                ],
            ],
        ];
        yield [
            [
                $this->mockSystemConfigEntity('sales_channel_1', 'api_key_1'),
                $this->mockSystemConfigEntity('sales_channel_2', 'api_key_2'),
                $this->mockSystemConfigEntity('sales_channel_3', 'api_key_3'),
            ],
            [
                [
                    'some_base_url',
                    'sales_channel_1',
                    'api_key_1',
                ],
                [
                    'some_base_url',
                    'sales_channel_2',
                    'api_key_2',
                ],
                [
                    'some_base_url',
                    'sales_channel_3',
                    'api_key_3',
                ],
            ],
        ];
        yield [
            [
                $this->mockSystemConfigEntity('sales_channel_1', 'api_key_1'),
                $this->mockSystemConfigEntity(null, 'api_key_2'),
                $this->mockSystemConfigEntity('sales_channel_3', 'api_key_3'),
            ],
            [
                [
                    'some_base_url',
                    'sales_channel_1',
                    'api_key_1',
                ],
                [
                    'some_base_url',
                    'sales_channel_3',
                    'api_key_3',
                ],
            ],
        ];
    }

    /**
     * @return SystemConfigEntity|MockObject
     */
    private function mockSystemConfigEntity(?string $salesChannelId, string $configurationValue): SystemConfigEntity
    {
        return $this->createConfiguredMock(SystemConfigEntity::class, [
            'getUniqueIdentifier' => Uuid::randomHex(),
            'getSalesChannelId' => $salesChannelId,
            'getConfigurationValue' => $configurationValue,
        ]);
    }

    private function mockSystemConfigServiceReturnsAccessData(): void
    {
        $this->systemConfigServiceMock->expects($this->exactly(2))
            ->method('getString')
            ->withConsecutive(
                ['StrixErgonode.config.ergonodeBaseUrl'],
                ['StrixErgonode.config.ergonodeApiKey'],
            )
            ->willReturnOnConsecutiveCalls(
                'some_base_url',
                'some_api_key'
            );
    }
}

<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Strix\Ergonode\Api\ErgonodeAccessData;
use Strix\Ergonode\Provider\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    /**
     * @var MockObject|SystemConfigService
     */
    private $systemConfigService;

    protected function setUp(): void
    {
        $this->systemConfigService = $this->createMock(SystemConfigService::class);

        $this->configProvider = new ConfigProvider(
            $this->systemConfigService
        );
    }

    public function testGetErgonodeAccessDataGetter()
    {
        $this->systemConfigService->expects($this->exactly(2))
            ->method('getString')
            ->withConsecutive(
                ['StrixErgonode.config.ergonodeBaseUrl'],
                ['StrixErgonode.config.ergonodeApiKey'],
            )
            ->willReturnOnConsecutiveCalls(
                'some_base_url',
                'some_api_key'
            );

        $result = $this->configProvider->getErgonodeAccessData();

        $this->assertInstanceOf(ErgonodeAccessData::class, $result);
        $this->assertEquals('some_base_url', $result->getBaseUrl());
        $this->assertEquals('some_api_key', $result->getApiKey());
    }
}

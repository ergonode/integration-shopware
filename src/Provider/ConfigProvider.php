<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Strix\Ergonode\Api\Client\ErgonodeAccessData;

class ConfigProvider
{
    private const STRIX_ERGONODE_CONFIG_NAMESPACE = 'StrixErgonode.config.';

    private SystemConfigService $configService;

    public function __construct(
        SystemConfigService $configService
    ) {
        $this->configService = $configService;
    }

    public function getErgonodeAccessData(): ErgonodeAccessData
    {
        return new ErgonodeAccessData(
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeBaseUrl'),
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeApiKey'),
        );
    }
}
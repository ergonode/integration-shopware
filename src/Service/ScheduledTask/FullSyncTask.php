<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class FullSyncTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ergonode.full_sync_task';
    }

    public static function getDefaultInterval(): int
    {
        return 60; // 1m
    }
}

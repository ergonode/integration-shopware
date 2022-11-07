<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CleanSyncHistoryTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ergonode.clear_sync_history_task';
    }

    public static function getDefaultInterval(): int
    {
        return 60 * 60 * 24 * 30; // 30d
    }
}

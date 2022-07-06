<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class AttributeSyncTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'strix.ergonode.attribute_sync_task';
    }

    public static function getDefaultInterval(): int
    {
        return 1800; // 30m
    }
}
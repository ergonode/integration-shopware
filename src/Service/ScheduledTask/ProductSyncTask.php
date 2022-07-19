<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductSyncTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ergonode.product_sync_task';
    }

    public static function getDefaultInterval(): int
    {
        return 1800; // 30m
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Doctrine\DBAL\Exception;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryCleaner;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class CleanSyncHistoryTaskHandler extends ScheduledTaskHandler
{
    private const INTERVAL_DAYS = 30;

    private SyncHistoryCleaner $cleaner;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryCleaner $cleaner
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->cleaner = $cleaner;
    }

    public static function getHandledMessages(): iterable
    {
        return [CleanSyncHistoryTask::class];
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->cleaner->clean(self::INTERVAL_DAYS);
    }
}

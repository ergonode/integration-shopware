<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Doctrine\DBAL\Exception;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryCleaner;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
//#[AsMessageHandler] - remove comment and `ScheduledTaskHandler` in SW 6.6.0
class CleanSyncHistoryTaskHandler extends ScheduledTaskHandler
{
    private const INTERVAL_DAYS = 30;

    private SyncHistoryCleaner $cleaner;

    public function __construct(
        SyncHistoryCleaner $cleaner,
        EntityRepository $scheduledTaskRepository
    ) {
        $this->cleaner = $cleaner;
        parent::__construct($scheduledTaskRepository);
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

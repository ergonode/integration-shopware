<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Doctrine\DBAL\Exception;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryCleaner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CleanSyncHistoryTaskHandler
{
    private const INTERVAL_DAYS = 30;

    private SyncHistoryCleaner $cleaner;

    public function __construct(
        SyncHistoryCleaner $cleaner
    ) {
        $this->cleaner = $cleaner;
    }

    /**
     * @throws Exception
     */
    public function __invoke(CleanSyncHistoryTask $message): void
    {
        $this->cleaner->clean(self::INTERVAL_DAYS);
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\LanguageSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class LanguageSyncTaskHandler extends AbstractSyncTaskHandler
{
    private LanguageSyncProcessor $languageSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        LanguageSyncProcessor $languageSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->languageSyncProcessor = $languageSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [LanguageSyncTask::class];
    }

    public function runSync(): int
    {
        $count = 0;

        try {
            $result = $this->languageSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
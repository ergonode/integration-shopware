<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\DeletedAttributesSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class DeletedAttributeSyncTaskHandler extends AbstractSyncTaskHandler
{
    private DeletedAttributesSyncProcessor $deletedAttributesSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryLogger,
        LockFactory $lockFactory,
        LoggerInterface $syncLogger,
        DeletedAttributesSyncProcessor $deletedAttributesSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryLogger, $lockFactory, $syncLogger);

        $this->deletedAttributesSyncProcessor = $deletedAttributesSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [DeletedAttributeSyncTask::class];
    }

    public function runSync(): int
    {
        $count = 0;

        try {
            $result = $this->deletedAttributesSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
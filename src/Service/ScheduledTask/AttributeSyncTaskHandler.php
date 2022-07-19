<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\AttributeSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class AttributeSyncTaskHandler extends AbstractSyncTaskHandler
{
    private AttributeSyncProcessor $attributeSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryLogger,
        LockFactory $lockFactory,
        LoggerInterface $syncLogger,
        AttributeSyncProcessor $attributeSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryLogger, $lockFactory, $syncLogger);

        $this->attributeSyncProcessor = $attributeSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [AttributeSyncTask::class];
    }

    public function runSync(): int
    {
        $count = 0;

        try {
            $result = $this->attributeSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
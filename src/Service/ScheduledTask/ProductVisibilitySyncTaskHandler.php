<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\ProductVisibilitySyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class ProductVisibilitySyncTaskHandler extends AbstractSyncTaskHandler
{
    private ProductVisibilitySyncProcessor $productVisibilitySyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ProductVisibilitySyncProcessor $productVisibilitySyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->productVisibilitySyncProcessor = $productVisibilitySyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [ProductVisibilitySyncTask::class];
    }

    public function runSync(): int
    {
        $count = 0;

        try {
            $result = $this->productVisibilitySyncProcessor->processStream($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\DeletedProductSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class DeletedProductSyncTaskHandler extends AbstractSyncTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private DeletedProductSyncProcessor $deletedProductSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        DeletedProductSyncProcessor $deletedProductSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->deletedProductSyncProcessor = $deletedProductSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [DeletedProductSyncTask::class];
    }

    public function runSync(): int
    {
        $currentPage = 0;
        $count = 0;

        try {
            do {
                $result = $this->deletedProductSyncProcessor->processStream($this->context);

                $count += $result->getProcessedEntityCount();

                if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                    break;
                }
            } while ($result->hasNextPage());
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedProductSync;
use Ergonode\IntegrationShopware\Processor\DeletedProductSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class DeletedProductSyncHandler extends AbstractSyncHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private DeletedProductSyncProcessor $deletedProductSyncProcessor;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        DeletedProductSyncProcessor $deletedProductSyncProcessor
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->deletedProductSyncProcessor = $deletedProductSyncProcessor;
    }

    public function __invoke(DeletedProductSync $message)
    {
        $this->handleMessage($message);
    }

    public function runSync($message): int
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

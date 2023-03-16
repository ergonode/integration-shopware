<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\ProductVisibilitySync;
use Ergonode\IntegrationShopware\Processor\ProductVisibilitySyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class ProductVisibilitySyncHandler extends AbstractSyncHandler
{
    private ProductVisibilitySyncProcessor $productVisibilitySyncProcessor;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ProductVisibilitySyncProcessor $productVisibilitySyncProcessor
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->productVisibilitySyncProcessor = $productVisibilitySyncProcessor;
    }

    public function __invoke(ProductVisibilitySync $message)
    {
        $this->handleMessage($message);
    }

    public function runSync($message): int
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

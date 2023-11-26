<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\ProductVisibilitySync;
use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductVisibilitySync;
use Ergonode\IntegrationShopware\Processor\ProductVisibilitySyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class SingleProductVisibilitySyncHandler extends AbstractSyncHandler
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

    public function __invoke(SingleProductVisibilitySync $message)
    {
        $this->handleMessage($message);
    }

    public function runSync($message): int
    {
        if (!$message instanceof SingleProductVisibilitySync) {
            throw new \Exception('Invalid sync message');
        }
        $count = 0;

        try {
            $result = $this->productVisibilitySyncProcessor->processSingle($message->getSkuMap(), $this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }


    protected function getLockName(): string
    {
        $expl = explode('\\', SingleProductVisibilitySync::class);
        $className = end($expl);
        return sprintf('%s.%s.%s', 'ErgonodeIntegration', $className, 'lock');
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductSync;
use Ergonode\IntegrationShopware\Processor\ProductSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class SingleProductSyncHandler extends AbstractSyncHandler
{
    private const MAX_PAGES_PER_RUN = 4;

    private ProductSyncProcessor $productSyncProcessor;
    private MessageBusInterface $messageBus;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        ProductSyncProcessor $productSyncProcessor,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->productSyncProcessor = $productSyncProcessor;
        $this->messageBus = $messageBus;
    }

    public static function getHandledMessages(): iterable
    {
        return [SingleProductSync::class];
    }

    protected function createContext(): Context
    {
        $context = parent::createContext();
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        return $context;
    }

    public function handle($message): void
    {
        if (!$message instanceof SingleProductSync) {
            throw new \RuntimeException('Invalid message handled');
        }
        $lock = $this->lockFactory->createLock($this->getLockName());
        if (false === $lock->acquire()) {
            $this->logger->error(sprintf('%s is locked.', $this->getTaskName()));

            return;
        }

        $id = $this->syncHistoryService->start($this->getTaskName(), $this->context);

        $count = $this->runSyncWithMessage($message);

        $this->syncHistoryService->finish($id, $this->context, $count);
    }

    public function runSync(): int
    {
        // do nothing, use runSyncWithMessage as message is required for this handler
        return 0;
    }

    public function runSyncWithMessage(SingleProductSync $message): int
    {
        $currentPage = 0;
        $count = 0;
        $result = null;

        try {
            $primaryKeys = [];
            do {
                $result = $this->productSyncProcessor->processSingle($message->getSku(), $this->context);

                $count += $result->getProcessedEntityCount();
                $primaryKeys = \array_merge($primaryKeys, $result->getPrimaryKeys());


                if (self::MAX_PAGES_PER_RUN !== null && ++$currentPage >= self::MAX_PAGES_PER_RUN) {
                    break;
                }
            } while ($result->hasNextPage());
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching product indexing message');
            $indexingMessage = new ProductIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('product.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        if (null !== $result) {
            if ($result->hasNextPage()) {
                $this->messageBus->dispatch(new SingleProductSync($message->getSku()));
            } else {
                $this->productSyncProcessor->deleteOrphanedVariants($message->getSku(), $this->context);
            }
        }

        return $count;
    }
}

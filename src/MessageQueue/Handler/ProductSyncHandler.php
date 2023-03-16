<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\ProductSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductSync;
use Ergonode\IntegrationShopware\Processor\ProductSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler]
class ProductSyncHandler extends AbstractSyncHandler
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

    public function __invoke(ProductSync $message)
    {
        $this->handleMessage($message);
    }

    protected function createContext($message): Context
    {
        $context = parent::createContext($message);
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        return $context;
    }

    public function runSync($message): int
    {
        $currentPage = 0;
        $count = 0;
        $result = null;

        try {
            $primaryKeys = [];
            do {
                $result = $this->productSyncProcessor->processStream($this->context);

                $count += $result->getProcessedEntityCount();
                $primaryKeys = \array_merge($primaryKeys, $result->getPrimaryKeys());

                foreach ($result->getSeparateProcessSkus() as $sku) {
                    $this->messageBus->dispatch(new SingleProductSync($sku, true));
                }

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

        if (null !== $result && $result->hasNextPage()) {
            $this->messageBus->dispatch(new ProductSync());
        }

        return $count;
    }
}

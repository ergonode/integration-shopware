<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\SingleProductCategorySync;
use Ergonode\IntegrationShopware\Persistor\ProductCategoryPersistor;
use Ergonode\IntegrationShopware\Processor\ProductCategorySyncProcessor;
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
class ProductCategorySyncHandler extends AbstractSyncHandler
{
    private ProductCategorySyncProcessor $processor;

    private ProductCategoryPersistor $persistor;

    private MessageBusInterface $messageBus;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        ProductCategorySyncProcessor $processor,
        ProductCategoryPersistor $persistor,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->processor = $processor;
        $this->persistor = $persistor;
        $this->messageBus = $messageBus;
    }

    public function __invoke(SingleProductCategorySync $message)
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
        $count = 0;
        $records = [];
        $result = null;

        try {
            do {
                $result = $this->processor->process($message->getSku(), $this->context);

                $count += $result->getProcessedEntityCount();
                $records = \array_merge($records, $result->getRetrievedData());

            } while ($result->hasNextPage());


        $primaryKeys = $this->persistor->persist($message->getSku(), $records, $this->context);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching product indexing message');
            $indexingMessage = new ProductIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('product.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        return $count;
    }
}

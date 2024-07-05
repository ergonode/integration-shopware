<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Doctrine\DBAL\Connection;
use Ergonode\IntegrationShopware\MessageQueue\Message\CategoryAttributesSync;
use Ergonode\IntegrationShopware\Processor\CategoryAttributesSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Ergonode\IntegrationShopware\Struct\CategoryContainer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\DataAbstractionLayer\CategoryIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler]
class CategoryAttributeSyncHandler extends AbstractSyncHandler
{
    private CategoryAttributesSyncProcessor $processor;

    private MessageBusInterface $messageBus;

    public function __construct(
        SyncHistoryLogger               $syncHistoryService,
        LoggerInterface                 $ergonodeSyncLogger,
        LockFactory                     $lockFactory,
        CategoryAttributesSyncProcessor $processor,
        MessageBusInterface             $messageBus,
        private readonly Connection     $connection,
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->processor = $processor;
        $this->messageBus = $messageBus;
    }

    public function __invoke(CategoryAttributesSync $message)
    {
        $this->handleMessage($message);
    }

    public static function getHandledMessages(): iterable
    {
        return [CategoryAttributesSync::class];
    }

    protected function createContext($message): Context
    {
        $context = parent::createContext($message);
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        return $context;
    }

    public function runSync($message): int
    {
        $categoryContainer = $this->createCategoryContainer();

        $primaryKeys = [];
        do {
            try {
                $result = $this->processor->processStream($categoryContainer, $this->context);

                $primaryKeys = array_merge($primaryKeys, $result->getPrimaryKeys());
            } catch (Throwable $e) {
                $this->logger->error('Error while persisting category sync.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } while (isset($result) && $result->hasNextPage());

        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching category indexing message');
            $indexingMessage = new CategoryIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('category.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        return count($primaryKeys);
    }

    private function createCategoryContainer(): CategoryContainer
    {
        $categories = $this->connection->fetchAllAssociative(
            'SELECT code, LOWER(HEX(c.id)) as id  
                    FROM ergonode_category_mapping_extension ecme 
                    INNER JOIN category c ON c.ergonode_category_mapping_extension_id = ecme.id'
        );

        return new CategoryContainer(array_column($categories, 'id', 'code'));
    }
}

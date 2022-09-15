<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Persistor\CategoryPersistor;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Processor\CategoryProcessorInterface;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\DataAbstractionLayer\CategoryIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class CategorySyncTaskHandler extends AbstractSyncTaskHandler
{
    private const MAX_PAGES_PER_RUN = 10;

    private ConfigProvider $configProvider;

    private iterable $processors;

    private MessageBusInterface $messageBus;

    private SyncPerformanceLogger $performanceLogger;

    private CategoryOrderHelper $categoryOrderHelper;

    private CategoryPersistor $categoryPersistor;

    /**
     * @param CategoryProcessorInterface[] $processors
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ConfigProvider $configProvider,
        iterable $processors,
        MessageBusInterface $messageBus,
        SyncPerformanceLogger $performanceLogger,
        CategoryOrderHelper $categoryOrderHelper,
        CategoryPersistor $categoryPersistor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->configProvider = $configProvider;
        $this->processors = $processors;
        $this->messageBus = $messageBus;
        $this->performanceLogger = $performanceLogger;
        $this->categoryOrderHelper = $categoryOrderHelper;
        $this->categoryPersistor = $categoryPersistor;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategorySyncTask::class];
    }

    protected function createContext(): Context
    {
        $context = parent::createContext();
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        return $context;
    }

    public function runSync(): int
    {
        $currentPage = 0;
        $count = 0;

        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            $this->logger->error('Could not find category tree code in plugin config.');

            return 0;
        }

        $result = null;
        $primaryKeys = [];
        try {
            foreach ($this->processors as $processor) {
                $processorClass = \get_class($processor);
                $this->logger->info('Starting category processor', [
                    'processor' => $processorClass
                ]);

                do {
                    $result = $processor->processStream($categoryTreeCode, $this->context);

                    if ($result->hasStopwatch()) {
                        $this->performanceLogger->logPerformance($processorClass, $result->getStopwatch());
                    }

                    $count += $result->getProcessedEntityCount();
                    $primaryKeys = \array_merge($primaryKeys, $result->getPrimaryKeys());

                    if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                        break 2;
                    }
                } while ($result->hasNextPage());
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        $primaryKeys = \array_unique($primaryKeys);
        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching category indexing message');
            $indexingMessage = new CategoryIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('category.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        if (null !== $result && $result->hasNextPage()) {
            $this->logger->info('Dispatching next CategorySyncMessage because still has next page');
            $this->messageBus->dispatch(new CategorySyncTask());
        } else {
            $this->logger->info('Category sync finished. Clearing Category Order Helper saved mappings');
            $this->categoryOrderHelper->clearSaved();

            $lastSync = $this->configProvider->getLastCategorySyncTimestamp();
            $removedCategoryCount = $this->categoryPersistor->removeCategoriesUpdatedAtBeforeTimestamp($lastSync);

            $this->logger->info('Removed orphaned Ergonode categories', [
                'count' => $removedCategoryCount
            ]);

            $formattedTime = $this->configProvider->setLastCategorySyncTimestamp(\time());
            $this->logger->info('Saved lastCategorySyncTime', [
                'time' => $formattedTime
            ]);
        }

        return $count;
    }
}
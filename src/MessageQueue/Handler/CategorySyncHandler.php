<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\Api\CategoryStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\MessageQueue\Message\CategorySync;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Processor\CategoryProcessorInterface;
use Ergonode\IntegrationShopware\Processor\CategorySyncProcessor;
use Ergonode\IntegrationShopware\Processor\CategoryTreeSyncProcessor;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\DataAbstractionLayer\CategoryIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler]
class CategorySyncHandler extends AbstractSyncHandler
{
    private const MAX_PAGES_PER_RUN = 10;

    private ConfigService $configService;

    private CategoryTreeSyncProcessor $processor;

    private MessageBusInterface $messageBus;

    private SyncPerformanceLogger $performanceLogger;

    private CategoryOrderHelper $categoryOrderHelper;

    private EntityRepository $ergonodeCategoryMappingRepository;

    private ErgonodeCursorManager $cursorManager;

    /**
     * @param SyncHistoryLogger $syncHistoryService
     * @param LoggerInterface $ergonodeSyncLogger
     * @param LockFactory $lockFactory
     * @param ConfigService $configService
     * @param CategoryTreeSyncProcessor $processor
     * @param MessageBusInterface $messageBus
     * @param SyncPerformanceLogger $performanceLogger
     * @param CategoryOrderHelper $categoryOrderHelper
     * @param ErgonodeCursorManager $cursorManager
     */
    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ConfigService $configService,
        CategoryTreeSyncProcessor $processor,
        MessageBusInterface $messageBus,
        SyncPerformanceLogger $performanceLogger,
        CategoryOrderHelper $categoryOrderHelper,
        EntityRepository $ergonodeCategoryMappingRepository,
        ErgonodeCursorManager $cursorManager
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->configService = $configService;
        $this->processor = $processor;
        $this->messageBus = $messageBus;
        $this->performanceLogger = $performanceLogger;
        $this->categoryOrderHelper = $categoryOrderHelper;
        $this->ergonodeCategoryMappingRepository = $ergonodeCategoryMappingRepository;
        $this->cursorManager = $cursorManager;
    }

    public function __invoke(CategorySync $message)
    {
        $this->handleMessage($message);
    }

    public static function getHandledMessages(): iterable
    {
        return [CategorySync::class];
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

        $categoryTreeCodes = $this->configService->getCategoryTreeCodes();
        if (empty($categoryTreeCodes)) {
            $this->logger->error('Could not find category tree codes in plugin config.');

            return 0;
        }

        $this->clearLegacyCategoryMappings();
        $primaryKeys = [];
        try {
            $processedKeys = $this->runProcessor($categoryTreeCodes);

            $primaryKeys = \array_merge($primaryKeys, $processedKeys);
            $count += count($primaryKeys);
        } catch (Throwable $e) {
            $this->logger->error('Error while persisting category sync.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $primaryKeys = \array_unique($primaryKeys);
        if (false === empty($primaryKeys)) {
            $this->logger->info('Dispatching category indexing message');
            $indexingMessage = new CategoryIndexingMessage($primaryKeys);
            $indexingMessage->setIndexer('category.indexer');
            $this->messageBus->dispatch($indexingMessage);
        }

        return $count;
    }

    private function runProcessor(array $categoryTreeCodes): array
    {
        $currentPage = 0;
        $processorClass = \get_class($this->processor);
        $this->logger->info(
            'Starting category processor',
            [
                'processor' => $processorClass,
                'category_tree_codes' => $categoryTreeCodes,
            ]
        );

        $primaryKeys = [];
        do {
            $result = $this->processor->processStream($categoryTreeCodes, $this->context);

            if ($result->hasStopwatch()) {
                $this->performanceLogger->logPerformance($processorClass, $result->getStopwatch());
            }

            $primaryKeys[] = $result->getPrimaryKeys();

            $currentPage++;
        } while ($result->hasNextPage() && $currentPage <= self::MAX_PAGES_PER_RUN);

        $primaryKeys = array_merge(...$primaryKeys);

        if (null !== $result && $result->hasNextPage()) {
            $this->logger->info('Dispatching next CategorySyncMessage because still has next page');
            $this->messageBus->dispatch(new CategorySync());
        } else {
            $this->processor->removeOrphanedCategories($this->context);

            $formattedTime = $this->configService->setLastCategorySyncTimestamp(
                (new \DateTime('+1 second'))->getTimestamp()
            );
            $this->logger->info('Saved lastCategorySyncTime', [
                'time' => $formattedTime,
            ]);
            $this->categoryOrderHelper->clearSaved();
            $this->cursorManager->deleteCursors(
                [
                    CategoryStreamResultsProxy::MAIN_FIELD,
                    CategoryTreeStreamResultsProxy::MAIN_FIELD,
                    CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                ],
                $this->context
            );
            $this->logger->info('Category sync finished.');
        }

        return $primaryKeys;
    }

    /**
     * Clears legacy mapping records which don't have category associated with
     */
    private function clearLegacyCategoryMappings(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('category.id', null));
        $mappingIds = $this->ergonodeCategoryMappingRepository->searchIds($criteria, $this->context);
        if (!empty($mappingIds->getIds())) {
            $this->ergonodeCategoryMappingRepository->delete(array_values($mappingIds->getData()), $this->context);
        }
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\CategorySync;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Processor\CategoryProcessorInterface;
use Ergonode\IntegrationShopware\Processor\CategoryTreeSyncProcessor;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\DataAbstractionLayer\CategoryIndexingMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class CategorySyncHandler extends AbstractSyncHandler
{
    private const MAX_PAGES_PER_RUN = 10;

    private ConfigService $configService;

    private iterable $processors;

    private MessageBusInterface $messageBus;

    private SyncPerformanceLogger $performanceLogger;

    private CategoryOrderHelper $categoryOrderHelper;

    /**
     * @param SyncHistoryLogger $syncHistoryService
     * @param LoggerInterface $ergonodeSyncLogger
     * @param LockFactory $lockFactory
     * @param ConfigService $configService
     * @param CategoryProcessorInterface[] $processors
     * @param MessageBusInterface $messageBus
     * @param SyncPerformanceLogger $performanceLogger
     * @param CategoryOrderHelper $categoryOrderHelper
     */
    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ConfigService $configService,
        iterable $processors,
        MessageBusInterface $messageBus,
        SyncPerformanceLogger $performanceLogger,
        CategoryOrderHelper $categoryOrderHelper
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->configService = $configService;
        $this->processors = $processors;
        $this->messageBus = $messageBus;
        $this->performanceLogger = $performanceLogger;
        $this->categoryOrderHelper = $categoryOrderHelper;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategorySync::class];
    }

    protected function createContext(): Context
    {
        $context = parent::createContext();
        $context->addState(EntityIndexerRegistry::DISABLE_INDEXING);

        return $context;
    }

    public function runSync(): int
    {
        $count = 0;

        $categoryTreeCodes = $this->configService->getCategoryTreeCodes();
        if (empty($categoryTreeCodes)) {
            $this->logger->error('Could not find category tree codes in plugin config.');

            return 0;
        }

        $primaryKeys = [];
        try {
            foreach ($this->processors as $processor) {
                $processedKeys = $this->runProcessor($categoryTreeCodes, $processor);

                $primaryKeys = \array_merge($primaryKeys, $processedKeys);
                $count += count($primaryKeys);
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

        return $count;
    }

    private function runProcessor(array $categoryTreeCodes, CategoryProcessorInterface $processor): array
    {
        $currentPage = 0;
        $processorClass = \get_class($processor);
        $this->logger->info(
            'Starting category processor',
            [
                'processor' => $processorClass,
                'category_tree_codes' => $categoryTreeCodes
            ]
        );

        $primaryKeys = [];
        do {
            $result = $processor->processStream($categoryTreeCodes, $this->context);

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
            if ($processor instanceof CategoryTreeSyncProcessor) {
                $processor->removeOrphanedCategories($categoryTreeCodes);

                $formattedTime = $this->configService->setLastCategorySyncTimestamp(
                    (new \DateTime('+1 second'))->getTimestamp()
                );
                $this->logger->info('Saved lastCategorySyncTime', [
                    'time' => $formattedTime,
                ]);
            }
            $this->logger->info('Category sync finished.');
            $this->categoryOrderHelper->clearSaved();
        }

        return $primaryKeys;
    }
}

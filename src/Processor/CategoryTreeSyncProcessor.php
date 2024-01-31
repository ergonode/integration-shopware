<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\CategoryTreePersistor;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryQueryBuilder;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;
use function count;

class CategoryTreeSyncProcessor implements CategoryProcessorInterface
{
    public const DEFAULT_LEAF_COUNT = 25;

    private ErgonodeGqlClientInterface $gqlClient;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private CategoryTreePersistor $categoryTreePersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    private CategoryOrderHelper $categoryOrderHelper;

    private ConfigService $configService;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryTreePersistor $categoryTreePersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
        CategoryOrderHelper $categoryOrderHelper,
        ConfigService $configService
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryTreePersistor = $categoryTreePersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
        $this->categoryOrderHelper = $categoryOrderHelper;
        $this->configService = $configService;
    }

    /**
     * @inheritDoc
     */
    public function processStream(
        array $treeCodes,
        Context $context
    ): SyncCounterDTO {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $leafCursor = $this->cursorManager->getCursor(
            CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
            $context
        );

        $stopwatch->start('query');
        $query = $this->categoryQueryBuilder->buildTreeStream(
            self::DEFAULT_LEAF_COUNT,
            $leafCursor
        );

        /** @var CategoryTreeStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryTreeStreamResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        $leafEdges = $result->getEdges()[0]['node']['categoryTreeLeafList']['edges'] ?? [];
        if (0 === count($result->getEdges()) && 0 === count($leafEdges)) {
            $this->logger->info('End of stream reached.');
            $counter->setHasNextPage(false);

            return $counter;
        }

        $treeEndCursor = $result->getEndCursor();
        if (null === $treeEndCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $leafHasNextPage = false;
        $leafEndCursor = null;
        $processedKeys = [];

        $this->fetchCategoryRootId($context);
        foreach ($result->getEdges() as $edge) {
            $node = $edge['node'] ?? null;
            $currentTreeCode = $node['code'];

            if (false === \in_array($currentTreeCode, $treeCodes)) {
                continue;
            }

            $stopwatch->start('process');

            try {
                $leafEdges = $edge['node']['categoryTreeLeafList']['edges'] ?? [];

                $primaryKeys = $this->categoryTreePersistor->persistLeaves($leafEdges, $currentTreeCode, $context);
                $this->categoryTreePersistor->markCategoriesAsActive($primaryKeys);

                $processedKeys[] = $primaryKeys;

                if (!$leafHasNextPage) {
                    $leafHasNextPage = $edge['node']['categoryTreeLeafList']['pageInfo']['hasNextPage'] ?? false;
                    $leafEndCursor = $edge['node']['categoryTreeLeafList']['pageInfo']['endCursor'] ?? null;
                    // Restore code removed in SWERG-174.
                    // fix for SWERG-169. The issue is that removeOrphanedCategories adds 1 second to the
                    // last sync time and when the sync is ran for the first time some trees can be processed under
                    // 1 second resulting in them being completely removed because they have updated_at time before
                    // the new lastSyncTime
                    sleep(2);
                }

                $this->logger->info('Persisted category leaves', [
                    'count' => count($primaryKeys),
                    'treeCode' => $currentTreeCode
                ]);
            } catch (Throwable $e) {
                $this->logger->error('Error while persisting category leaves.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'code' => $node['code'],
                ]);
            } finally {
                $stopwatch->stop('process');
            }
        }

        $processedKeys = array_merge(...$processedKeys);
        $entityCount = \count($processedKeys);

        $counter->incrProcessedEntityCount($entityCount);
        $counter->setPrimaryKeys($processedKeys);
        $this->cursorManager->persist(
            $treeEndCursor,
            CategoryTreeStreamResultsProxy::MAIN_FIELD,
            $context
        );
        if ($leafHasNextPage) {
            $this->logger->info('Category leaves have next page', [
                'leafCursor' => $leafEndCursor,
            ]);
            $this->cursorManager->persist(
                $leafEndCursor,
                CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                $context
            );
        } else {
            $this->cursorManager->deleteCursor(
                CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                $context
            );
        }

        $counter->setHasNextPage($result->hasNextPage() || $leafHasNextPage);
        $counter->setStopwatch($stopwatch);

        return $counter;
    }

    /**
     * Gets ID of last existing top level category
     *
     * @param Context $context
     * @return void
     */
    private function fetchCategoryRootId(Context $context): void
    {
        $this->categoryTreePersistor->resetLastRootCategoryId();
        $lastRootCategoryId = $this->categoryOrderHelper->getLastRootCategoryId($context);
        if ($lastRootCategoryId) {
            $this->categoryTreePersistor->setLastRootCategoryId($lastRootCategoryId);
        }
    }

    public function removeOrphanedCategories(Context $context): void
    {
        $lastSync = $this->configService->getLastCategorySyncTimestamp();

        $categoriesToDelete = $this->categoryTreePersistor->fetchCategoriesToDelete();

        try {
            $this->categoryTreePersistor->removeCategoriesById(
                array_values($categoriesToDelete),
                $context
            );
        } catch (Throwable $ex) {
            $this->logger->error('One of categories marked to delete is mapped in Sales Channel navigation. ' .
                'Cannot delete as long as it is set in sales channel navigation', [
                'categories' => $categoriesToDelete,
                'message' => $ex->getMessage(),
            ]);
        }
        $this->categoryTreePersistor->clearCategoriesAsActive();

        $this->logger->info('Removed orphaned Ergonode categories', [
            'count' => count($categoriesToDelete),
            'time' => (new \DateTime('@' . $lastSync))->format(DATE_ATOM),
        ]);
    }
}

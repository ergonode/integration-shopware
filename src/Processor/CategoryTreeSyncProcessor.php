<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\CategoryPersistor;
use Ergonode\IntegrationShopware\Persistor\CategoryTreePersistor;
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
    public const DEFAULT_TREE_COUNT = 1;
    public const DEFAULT_LEAF_COUNT = 25;

    private ErgonodeGqlClientInterface $gqlClient;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private CategoryTreePersistor $categoryTreePersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    private ConfigService $configService;

    private CategoryPersistor $categoryPersistor;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryTreePersistor $categoryTreePersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
        ConfigService $configService,
        CategoryPersistor $categoryPersistor
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryTreePersistor = $categoryTreePersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
        $this->configService = $configService;
        $this->categoryPersistor = $categoryPersistor;
    }

    /**
     * @inheritDoc
     */
    public function processStream(
        array $treeCodes,
        Context $context,
        ?int $categoryCount = null
    ): SyncCounterDTO {
        $categoryCount = $categoryCount ?? self::DEFAULT_LEAF_COUNT;
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $treeCursor = $this->cursorManager->getCursor(
            CategoryTreeStreamResultsProxy::MAIN_FIELD,
            $context
        );
        $leafCursor = $this->cursorManager->getCursor(
            CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
            $context
        );

        $stopwatch->start('query');
        $query = $this->categoryQueryBuilder->buildTreeStream(
            self::DEFAULT_TREE_COUNT,
            $categoryCount,
            $treeCursor,
            $leafCursor
        );

        /** @var CategoryTreeStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryTreeStreamResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        $leafEdges = $result->getEdges()[0]['node']['categoryTreeLeafList']['edges'] ?? [];
        $leafHasNextPage = $result->getEdges()[0]['node']['categoryTreeLeafList']['pageInfo']['hasNextPage'] ?? false;
        $leafEndCursor = $result->getEdges()[0]['node']['categoryTreeLeafList']['pageInfo']['endCursor'] ?? null;

        if (0 === count($result->getEdges()) && 0 === count($leafEdges)) {
            $this->logger->info('End of stream reached.');
            $counter->setHasNextPage(false);

            return $counter;
        }

        $treeEndCursor = $result->getEndCursor();
        if (null === $treeEndCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        foreach ($result->getEdges() as $edge) {
            $node = $edge['node'] ?? null;
            $currentTreeCode = $node['code'];

            if (false === \in_array($node['code'], $treeCodes)) {
                continue;
            }

            $stopwatch->start('process');

            try {
                $primaryKeys = $this->categoryTreePersistor->persistLeaves($leafEdges, $currentTreeCode, $context);

                if (false === $leafHasNextPage) {
                    $this->removeOrphanedCategories($currentTreeCode);

                    // TODO:
                    // Temporary fix for SWERG-169. The issue is that removeOrphanedCategories adds 1 second to the
                    // last sync time and when the sync is ran for the first time some trees can be processed under
                    // 1 second resulting in them being completely removed because they have updated_at time before
                    // the new lastSyncTime
                    sleep(2);
                }

                $entityCount = \count($primaryKeys);

                $counter->incrProcessedEntityCount($entityCount);
                $counter->setPrimaryKeys($primaryKeys);

                $this->logger->info('Persisted category leaves', [
                    'count' => $entityCount,
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

            break;
        }

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
            $this->logger->info('Category leaves do not have next page', [
                'treeCursor' => $treeEndCursor,
            ]);
            $this->cursorManager->deleteCursor(
                CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                $context
            );
            $this->cursorManager->persist(
                $treeEndCursor,
                CategoryTreeStreamResultsProxy::MAIN_FIELD,
                $context
            );
        }

        $counter->setHasNextPage($result->hasNextPage() || $leafHasNextPage);
        $counter->setStopwatch($stopwatch);

        return $counter;
    }

    private function removeOrphanedCategories(string $treeCode): void
    {
        $lastSync = $this->configService->getLastCategorySyncTimestamp();
        $removedCategoryCount = $this->categoryPersistor->removeCategoriesUpdatedAtBeforeTimestamp(
            $lastSync,
            $treeCode
        );

        $this->logger->info('Removed orphaned Ergonode categories', [
            'treeCode' => $treeCode,
            'count' => $removedCategoryCount,
            'time' => (new \DateTime('@' . $lastSync))->format(DATE_ATOM),
        ]);

        $formattedTime = $this->configService->setLastCategorySyncTimestamp(
            (new \DateTime('+1 second'))->getTimestamp()
        );
        $this->logger->info('Saved lastCategorySyncTime', [
            'time' => $formattedTime,
        ]);
    }
}
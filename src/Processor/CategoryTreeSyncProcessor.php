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

    private CategoryPersistor $categoryPersistor;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryTreePersistor $categoryTreePersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
        CategoryPersistor $categoryPersistor
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryTreePersistor = $categoryTreePersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
        $this->categoryPersistor = $categoryPersistor;
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

        $treeEndCursor = $result->getEndCursor();
        if (null === $treeEndCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $leafHasNextPage = false;
        $leafEndCursor = null;
        $processedKeys = [];
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

                $processedKeys[] = $primaryKeys;

                if (!$leafHasNextPage) {
                    $leafHasNextPage = ['node']['categoryTreeLeafList']['pageInfo']['hasNextPage'] ?? false;
                    $leafEndCursor = $edge['node']['categoryTreeLeafList']['pageInfo']['endCursor'] ?? null;
                }

                $this->logger->info('Persisted category leaves', [
                    'count' => count($primaryKeys),
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

    public function removeOrphanedCategories(array $keys): void
    {
        $removedCategoryCount = $this->categoryPersistor->removeOtherCategoriesFromTree(
            $keys
        );

        $this->logger->info('Removed orphaned Ergonode categories', [
            'count' => $removedCategoryCount,
            'time' => (new \DateTime('now'))->format(DATE_ATOM),
        ]);
    }

    public static function getDefaultPriority(): int
    {
        return 5;
    }
}

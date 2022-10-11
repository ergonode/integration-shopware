<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\CategoryStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\CategoryPersistor;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryQueryBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;
use function count;

class CategorySyncProcessor implements CategoryProcessorInterface
{
    public const DEFAULT_CATEGORY_COUNT = 10;

    private ErgonodeGqlClientInterface $gqlClient;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private CategoryPersistor $categoryPersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryPersistor $categoryPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryPersistor = $categoryPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
    }

    /**
     * @inheritDoc
     * @param int|null $categoryCount Number of categories to process (categories per page)
     */
    public function processStream(
        array $treeCodes,
        Context $context,
        ?int $categoryCount = null
    ): SyncCounterDTO {
        $categoryCount = $categoryCount ?? self::DEFAULT_CATEGORY_COUNT;
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $cursorEntity = $this->cursorManager->getCursorEntity(
            CategoryStreamResultsProxy::MAIN_FIELD,
            $context
        );
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $stopwatch->start('query');
        $query = $this->categoryQueryBuilder->build($categoryCount, $cursor);
        /** @var CategoryStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryStreamResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        if (0 === count($result->getEdges())) {
            $this->logger->info('End of stream reached.');
            $counter->setHasNextPage(false);

            return $counter;
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $stopwatch->start('process');
        try {
            $primaryKeys = $this->categoryPersistor->persist($result->getEdges(), $context);
            $entityCount = \count($primaryKeys);
            $counter->incrProcessedEntityCount($entityCount);
            $counter->setPrimaryKeys($primaryKeys);

            $this->logger->info('Persisted category translations', [
                'count' => $entityCount,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Error while persisting category translations.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);
        } finally {
            $stopwatch->stop('process');
        }

        $this->cursorManager->persist(
            $endCursor,
            CategoryStreamResultsProxy::MAIN_FIELD,
            $context
        );

        $counter->setHasNextPage($result->hasNextPage());
        $counter->setStopwatch($stopwatch);

        return $counter;
    }
}
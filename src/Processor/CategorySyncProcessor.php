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
use Throwable;

use function count;

class CategorySyncProcessor
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
        LoggerInterface $syncLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryPersistor = $categoryPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $syncLogger;
    }

    /**
     * @param int $categoryCount Number of categories to process (categories per page)
     */
    public function processStream(
        string $treeCode,
        Context $context,
        int $categoryCount = self::DEFAULT_CATEGORY_COUNT
    ): ?SyncCounterDTO {
        $counter = new SyncCounterDTO();

        $cursorEntity = $this->cursorManager->getCursorEntity(CategoryStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->categoryQueryBuilder->build($treeCode, $categoryCount, $cursor);
        /** @var CategoryStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryStreamResultsProxy::class);

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        if (0 === count($result->getEdges())) {
            $this->logger->info('End of stream reached.');
            return null;
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        foreach ($result->getEdges() as $edge) {
            $node = $edge['node'] ?? null;
            try {
                $this->categoryPersistor->persist($node, $context);

                $this->logger->info('Processed category.', [
                    'code' => $node['code'],
                ]);

                $counter->incrProcessedEntityCount();
            } catch (Throwable $e) {
                $this->logger->error('Error while persisting category.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'code' => $node['code'] ?? null,
                ]);
            }
        }

        $this->cursorManager->persist($endCursor, CategoryStreamResultsProxy::MAIN_FIELD, $context);

        $counter->setHasNextPage($result->hasNextPage());

        return $counter;
    }
}
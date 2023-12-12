<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\CategoryStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\CategoryAttributesPersistor;
use Ergonode\IntegrationShopware\QueryBuilder\CategoryQueryBuilder;
use Ergonode\IntegrationShopware\Struct\CategoryContainer;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;

class CategoryAttributesSyncProcessor
{
    public const DEFAULT_COUNT = 25;

    private ErgonodeGqlClientInterface $gqlClient;

    private CategoryQueryBuilder $categoryQueryBuilder;

    private CategoryAttributesPersistor $categoryAttributesPersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        CategoryQueryBuilder $categoryQueryBuilder,
        CategoryAttributesPersistor $categoryAttributesPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
    ) {
        $this->gqlClient = $gqlClient;
        $this->categoryQueryBuilder = $categoryQueryBuilder;
        $this->categoryAttributesPersistor = $categoryAttributesPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
    }

    /**
     * @inheritDoc
     */
    public function processStream(
        CategoryContainer $categoryContainer,
        Context $context
    ): SyncCounterDTO {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $categoryAttributeCursor = $this->cursorManager->getCursor(
            CategoryStreamResultsProxy::CATEGORY_ATTRIBUTES_LIST_CURSOR,
            $context
        );

        $stopwatch->start('query');
        $query = $this->categoryQueryBuilder->buildWithCategoryAttributes(
            self::DEFAULT_COUNT,
            $categoryAttributeCursor
        );

        /** @var CategoryStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, CategoryStreamResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        $treeEndCursor = $result->getEndCursor();
        if (null === $treeEndCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $primaryKeys = $this->categoryAttributesPersistor->persistCategoryAttributes(
            $result->getEdges(),
            $categoryContainer,
            $context
        );
        $counter->setPrimaryKeys($primaryKeys);

        if ($result->hasNextPage()) {
            $this->logger->info('Category attribute stream have next page', [
                'categoryAttributeCursor' => $categoryAttributeCursor,
            ]);
            $this->cursorManager->persist(
                $result->getEndCursor(),
                CategoryStreamResultsProxy::CATEGORY_ATTRIBUTES_LIST_CURSOR,
                $context
            );
        } else {
            $this->cursorManager->deleteCursor(
                CategoryStreamResultsProxy::CATEGORY_ATTRIBUTES_LIST_CURSOR,
                $context
            );
        }

        $counter->setHasNextPage($result->hasNextPage());
        $counter->setStopwatch($stopwatch);

        return $counter;
    }
}

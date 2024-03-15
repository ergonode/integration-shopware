<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductCategoryResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\QueryBuilder\ProductCategoryQueryBuilder;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;

use function count;

class ProductCategorySyncProcessor implements ProductCategorySyncProcessorInterface
{
    private ErgonodeGqlClientInterface $gqlClient;

    private ProductCategoryQueryBuilder $productCategoryQueryBuilder;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    private SyncPerformanceLogger $performanceLogger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        ProductCategoryQueryBuilder $productCategoryQueryBuilder,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
        SyncPerformanceLogger $performanceLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->productCategoryQueryBuilder = $productCategoryQueryBuilder;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
        $this->performanceLogger = $performanceLogger;
    }

    public function process(string $sku, Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $categoryCursorKey = CodeBuilderUtil::build(ProductCategoryResultsProxy::CATEGORY_LIST_FIELD, $sku);
        $categoryCursor = $this->cursorManager->getCursorEntity($categoryCursorKey, $context);

        $stopwatch->start('query');
        $query = $this->productCategoryQueryBuilder->build(
            $sku,
            $categoryCursor ? $categoryCursor->getCursor() : null
        );
        /** @var ProductCategoryResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductCategoryResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        if (0 === count($result->getMainData())) {
            $this->logger->info('End of categories reached.');

            return $counter;
        }

        $categoriesEndCursor = $result->getCategoriesEndCursor();

        $categories = $this->extractCategoryCodes($result->getProductData());

        if (null !== $categoriesEndCursor) {
            // Category cursor exists.
            $this->cursorManager->persist($categoriesEndCursor, $categoryCursorKey, $context);
        }

        $hasNextPage = $result->hasCategoriesNextPage();
        if (false === $result->hasCategoriesNextPage()) {
            // Nothing left remove cursors
            $this->cursorManager->deleteCursor($categoryCursorKey, $context);
        }

        $counter->incrProcessedEntityCount(count($categories));
        $counter->setRetrievedData($categories);
        $counter->setHasNextPage($hasNextPage);
        $counter->setStopwatch($stopwatch);

        $this->performanceLogger->logPerformance(self::class, $stopwatch);

        return $counter;
    }

    protected function extractCategoryCodes(array $productData): array
    {
        $result = [];
        foreach ($productData['categoryList']['edges'] as $record) {
            $result[] = $record['node']['code'];
        }

        return $result;
    }
}

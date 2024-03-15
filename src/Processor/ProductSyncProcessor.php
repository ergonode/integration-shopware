<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductResultsProxy;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\ProductPersistor;
use Ergonode\IntegrationShopware\QueryBuilder\ProductQueryBuilder;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;

use function count;

class ProductSyncProcessor
{
    public const DEFAULT_PRODUCT_COUNT = 10;

    private ErgonodeGqlClientInterface $gqlClient;

    private ProductQueryBuilder $productQueryBuilder;

    private ProductPersistor $productPersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    private SyncPerformanceLogger $performanceLogger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        ProductQueryBuilder $productQueryBuilder,
        ProductPersistor $productPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $ergonodeSyncLogger,
        SyncPerformanceLogger $performanceLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productPersistor = $productPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $ergonodeSyncLogger;
        $this->performanceLogger = $performanceLogger;
    }

    /**
     * @param Context $context
     * @param int $productCount
     *
     * @return SyncCounterDTO
     */
    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $cursorEntity = $this->cursorManager->getCursorEntity(ProductStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $stopwatch->start('query');
        $query = $this->productQueryBuilder->build($productCount, $cursor);
        /** @var ProductStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductStreamResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        if (0 === count($result->getProductData()['edges'])) {
            $this->logger->info('End of stream reached.');

            return $counter;
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $stopwatch->start('process');
        $primaryKeys = $this->productPersistor->persist($result->getProductData()['edges'], $context);
        $stopwatch->stop('process');

        $this->cursorManager->persist($endCursor, ProductStreamResultsProxy::MAIN_FIELD, $context);

        $separateProcessSkusVariants = [];
        $separateProcessSkusCategories = [];
        // store cursors for products which have more variants or categories than allowed limit in query builder
        foreach ($result->getProductData()['edges'] as $mainProductEdge) {
            $sku = $mainProductEdge['node']['sku'] ?? '';
            if (empty($sku)) {
                continue;
            }

            $hasMoreVariants = $this->saveCursor(
                $mainProductEdge['node'],
                ProductStreamResultsProxy::VARIANT_LIST_FIELD,
                $context
            );
            if (false === $hasMoreVariants) {
                $this->deleteOrphanedVariants($mainProductEdge['node']['sku'], $context);
            }

            $hasMoreCategories = $this->saveCursor(
                $mainProductEdge['node'],
                ProductStreamResultsProxy::CATEGORY_LIST_FIELD,
                $context
            );

            if ($hasMoreVariants) {
                $separateProcessSkusVariants[] = $sku;
            }

            if ($hasMoreCategories) {
                $separateProcessSkusCategories[] = $sku;
            }
        }

        $counter->incrProcessedEntityCount(count($primaryKeys));
        $counter->setPrimaryKeys($primaryKeys);
        $counter->setHasNextPage($result->hasNextPage());
        $counter->setStopwatch($stopwatch);
        $counter->setSeparateProcessSkusVariants($separateProcessSkusVariants);
        $counter->setSeparateProcessSkusCategories($separateProcessSkusCategories);

        $this->performanceLogger->logPerformance(self::class, $stopwatch);

        return $counter;
    }

    public function processSingle(string $sku, Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $variantsCursorKey = CodeBuilderUtil::build(ProductStreamResultsProxy::VARIANT_LIST_FIELD, $sku);

        $variantsCursor = $this->cursorManager->getCursorEntity($variantsCursorKey, $context);

        $stopwatch->start('query');
        $query = $this->productQueryBuilder->buildProductWithVariants(
            $sku,
            $variantsCursor ? $variantsCursor->getCursor() : null
        );
        /** @var ProductResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductResultsProxy::class);
        $stopwatch->stop('query');

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        if (0 === count($result->getProductData())) {
            $this->logger->info('End of stream reached.');

            return $counter;
        }

        $variantsEndCursor = $result->getVariantsEndCursor();
        $categoriesEndCursor = $result->getCategoriesEndCursor();

        $stopwatch->start('process');
        //extract category ids to pass to another processor
        $primaryKeys = $this->productPersistor->persist([['node' => $result->getProductData()]], $context);
        $stopwatch->stop('process');


        if (null !== $categoriesEndCursor) {
            //There is more categories that 50, pushing product to update categories
            $counter->setSeparateProcessSkusCategories([$sku]);
        }

        if (null !== $variantsEndCursor) {
            $this->cursorManager->persist($variantsEndCursor, $variantsCursorKey, $context);
        }

        $hasNextPage = $result->hasVariantsNextPage();
        if (false === $hasNextPage) {
            $this->cursorManager->deleteCursor($variantsCursorKey, $context);
        }

        $counter->incrProcessedEntityCount(count($primaryKeys));
        $counter->setPrimaryKeys($primaryKeys);
        $counter->setHasNextPage($hasNextPage);
        $counter->setStopwatch($stopwatch);

        $this->performanceLogger->logPerformance(self::class, $stopwatch);

        return $counter;
    }

    public function deleteOrphanedVariants(string $sku, Context $context): void
    {
        $query = $this->productQueryBuilder->buildVariantSkusForProduct($sku);
        /** @var ProductResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductResultsProxy::class);
        if (!$result) {
            $this->logger->debug(sprintf('No orphaned variants for product %s', $sku));

            return;
        }

        $this->productPersistor->deleteOrphanedSkus($sku, $context, $result->getVariants()['edges'] ?? []);
    }

    /**
     * @return bool $node['$fieldName']['pageInfo']['hasNextPage']
     */
    private function saveCursor(array $node, string $fieldName, Context $context): bool
    {
        if (empty($node)) {
            return false;
        }

        $sku = $node['sku'] ?? '';
        $pageInfo = $node[$fieldName]['pageInfo'] ?? [];
        if (empty($sku) || empty($pageInfo) || false === isset($pageInfo['hasNextPage'], $pageInfo['endCursor'])) {
            return false;
        }

        if ($pageInfo['hasNextPage']) {
            $this->cursorManager->persist(
                $pageInfo['endCursor'],
                CodeBuilderUtil::build($fieldName, $sku),
                $context
            );
        }

        return $pageInfo['hasNextPage'];
    }
}

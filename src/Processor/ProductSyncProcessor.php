<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductResultsProxy;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Exception\MissingRequiredProductMappingException;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\ProductPersistor;
use Ergonode\IntegrationShopware\QueryBuilder\ProductQueryBuilder;
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
     * @param int $productCount Number of products to process (products per page)
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

        $skusWithAdditionalVariants = [];
        // store cursors for products which have more variants than allowed limit in query builder
        foreach ($result->getProductData()['edges'] as $mainProductEdge) {
            $variantPageInfo = $mainProductEdge['node']['variantList']['pageInfo'] ?? [];
            if (empty($variantPageInfo)) {
                continue;
            }

            if (isset($variantPageInfo['hasNextPage'], $variantPageInfo['endCursor'], $mainProductEdge['node']['sku'])) {
                if ($variantPageInfo['hasNextPage']) {
                    $this->cursorManager->persist(
                        $variantPageInfo['endCursor'],
                        sprintf(ProductStreamResultsProxy::VARIANT_FIELD_PATTERN, $mainProductEdge['node']['sku']),
                        $context
                    );

                    $skusWithAdditionalVariants[] = $mainProductEdge['node']['sku'];
                } else {
                    $this->deleteOrphanedVariants($mainProductEdge['node']['sku'], $context);
                }
            }
        }

        $counter->incrProcessedEntityCount(count($primaryKeys));
        $counter->setPrimaryKeys($primaryKeys);
        $counter->setHasNextPage($result->hasNextPage());
        $counter->setStopwatch($stopwatch);
        $counter->setSkusWithAdditionalVariants($skusWithAdditionalVariants);

        $this->performanceLogger->logPerformance(self::class, $stopwatch);

        return $counter;
    }

    /**
     * @param string $sku
     * @param Context $context
     * @return SyncCounterDTO
     * @throws MissingRequiredProductMappingException
     */
    public function processSingle(string $sku, Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();

        $cursorEntity = $this->cursorManager->getCursorEntity(
            sprintf(ProductStreamResultsProxy::VARIANT_FIELD_PATTERN, $sku),
            $context
        );
        $cursor = $cursorEntity ? $cursorEntity->getCursor() : null;

        $stopwatch->start('query');
        $query = $this->productQueryBuilder->buildProductWithVariants($sku, $cursor);
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

        $stopwatch->start('process');
        $primaryKeys = $this->productPersistor->persist([['node' => $result->getProductData()]], $context);
        $stopwatch->stop('process');

        if ($result->hasNextPage()) {
            $this->cursorManager->persist(
                $result->getEndCursor(),
                sprintf(ProductStreamResultsProxy::VARIANT_FIELD_PATTERN, $sku),
                $context
            );
        } else {
            $this->cursorManager->deleteCursor(
                sprintf(ProductStreamResultsProxy::VARIANT_FIELD_PATTERN, $sku),
                $context
            );
        }

        $counter->incrProcessedEntityCount(count($primaryKeys));
        $counter->setPrimaryKeys($primaryKeys);
        $counter->setHasNextPage($result->hasNextPage());
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
}

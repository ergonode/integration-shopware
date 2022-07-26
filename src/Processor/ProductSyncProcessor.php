<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\ProductPersistor;
use Ergonode\IntegrationShopware\QueryBuilder\ProductQueryBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Throwable;

use function count;

class ProductSyncProcessor
{
    public const DEFAULT_PRODUCT_COUNT = 10;

    private ErgonodeGqlClientInterface $gqlClient;

    private ProductQueryBuilder $productQueryBuilder;

    private ProductPersistor $productPersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        ProductQueryBuilder $productQueryBuilder,
        ProductPersistor $productPersistor,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $syncLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productPersistor = $productPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $syncLogger;
    }

    /**
     * @param int $productCount Number of products to process (products per page)
     */
    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();

        $cursorEntity = $this->cursorManager->getCursorEntity(ProductStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->productQueryBuilder->build($productCount, $cursor);
        /** @var ProductStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductStreamResultsProxy::class);

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

        foreach ($result->getProductData()['edges'] as $edge) {
            $node = $edge['node'] ?? null;
            try {
                $productId = $this->productPersistor->persist($node, $context);

                $this->logger->info('Processed product.', [
                    'sku' => $node['sku'],
                    'productId' => $productId,
                ]);

                $counter->incrProcessedEntityCount();
            } catch (Throwable $e) {
                $this->logger->error('Error while persisting product.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'sku' => $node['sku'] ?? null,
                ]);
            }
        }

        $this->cursorManager->persist($endCursor, ProductStreamResultsProxy::MAIN_FIELD, $context);

        $counter->setHasNextPage($result->hasNextPage());

        return $counter;
    }
}
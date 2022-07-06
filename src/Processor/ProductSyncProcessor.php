<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Api\Client\ErgonodeGqlClientInterface;
use Strix\Ergonode\Manager\ErgonodeCursorManager;
use Strix\Ergonode\Modules\Product\Api\ProductStreamResultsProxy;
use Strix\Ergonode\Modules\Product\QueryBuilder\ProductQueryBuilder;
use Strix\Ergonode\Persistor\ProductPersistor;

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
     * @return bool Returns true if source has next page and false otherwise
     */
    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): bool
    {
        $cursorEntity = $this->cursorManager->getCursorEntity(ProductStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->productQueryBuilder->build($productCount, $cursor);
        /** @var ProductStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductStreamResultsProxy::class);

        if (null === $result) {
            throw new \RuntimeException('Request failed');
        }

        if (0 === \count($result->getProductData()['edges'])) {
            throw new \RuntimeException('End of stream');
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new \RuntimeException('Could not retrieve end cursor from the response');
        }

        foreach ($result->getProductData()['edges'] as $edge) {
            $node = $edge['node'] ?? null;
            try {
                $productId = $this->productPersistor->persist($node, $context);

                $this->logger->info('Processed product', [
                    'sku' => $node['sku'],
                    'productId' => $productId
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Error while persisting product', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'sku' => $node['sku'] ?? null,
                ]);
            }
        }

        $this->cursorManager->persist($endCursor, ProductStreamResultsProxy::MAIN_FIELD, $context);

        return $result->hasNextPage();
    }
}
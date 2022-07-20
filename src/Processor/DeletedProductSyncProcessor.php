<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\Api\Client\ErgonodeGqlClientInterface;
use Ergonode\IntegrationShopware\Api\ProductDeletedStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Persistor\ProductPersistor;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\QueryBuilder\ProductQueryBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use function count;

class DeletedProductSyncProcessor
{
    public const DEFAULT_PRODUCT_COUNT = 100;

    private ErgonodeGqlClientInterface $gqlClient;

    private ProductQueryBuilder $productQueryBuilder;

    private ProductPersistor $productPersistor;

    private ErgonodeCursorManager $cursorManager;

    private LoggerInterface $logger;

    private ProductProvider $productProvider;

    public function __construct(
        ErgonodeGqlClientInterface $gqlClient,
        ProductQueryBuilder $productQueryBuilder,
        ProductPersistor $productPersistor,
        ProductProvider $productProvider,
        ErgonodeCursorManager $cursorManager,
        LoggerInterface $syncLogger
    ) {
        $this->gqlClient = $gqlClient;
        $this->productQueryBuilder = $productQueryBuilder;
        $this->productPersistor = $productPersistor;
        $this->cursorManager = $cursorManager;
        $this->logger = $syncLogger;
        $this->productProvider = $productProvider;
    }

    /**
     * @param int $productCount Number of products to process (products per page)
     */
    public function processStream(Context $context, int $productCount = self::DEFAULT_PRODUCT_COUNT): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();

        $cursorEntity = $this->cursorManager->getCursorEntity(ProductDeletedStreamResultsProxy::MAIN_FIELD, $context);
        $cursor = null === $cursorEntity ? null : $cursorEntity->getCursor();

        $query = $this->productQueryBuilder->buildDeleted($productCount, $cursor);
        /** @var ProductStreamResultsProxy|null $result */
        $result = $this->gqlClient->query($query, ProductDeletedStreamResultsProxy::class);

        if (null === $result) {
            throw new RuntimeException('Request failed.');
        }

        $edges = $result->getData()['productDeletedStream']['edges'] ?? [];
        if (0 === count($edges)) {
            $this->logger->info('End of stream reached.');

            return $counter;
        }

        $endCursor = $result->getEndCursor();
        if (null === $endCursor) {
            throw new RuntimeException('Could not retrieve end cursor from the response.');
        }

        $deletedProductCodes = \array_map(
            fn($item) => $item['node'] ?? null,
            $edges
        );

        $idsToDelete = $this->productProvider->getProductIdsBySkus($deletedProductCodes, $context);
        $this->productPersistor->deleteProductIds($idsToDelete, $context);

        $this->logger->info('Processed deleted products', [
            'deletedProductCount' => \count($idsToDelete),
            'deletedProductCodes' => $deletedProductCodes,
            'deletedShopwareIds' => $idsToDelete
        ]);

        $this->cursorManager->persist($endCursor, ProductDeletedStreamResultsProxy::MAIN_FIELD, $context);

        $counter->setHasNextPage($result->hasNextPage());

        return $counter;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Throwable;

class ProductCategoryPersistor
{
    private EntityRepository $productRepository;

    private ProductProvider $productProvider;

    private CategoryProvider $categoryProvider;

    private LoggerInterface $logger;
    public function __construct(
        EntityRepository $productRepository,
        ProductProvider $productProvider,
        CategoryProvider $categoryProvider,
        LoggerInterface $ergonodeSyncLogger,
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->categoryProvider = $categoryProvider;
        $this->logger = $ergonodeSyncLogger;
    }

    /**
     * @returns array Persisted primary keys
     */
    public function persist(string $sku, array $categoryCodes, Context $context): array
    {
        $productId = $this->findProductIdBySku($sku, $context);

        if ($productId === null) {
            throw new \Exception('Product not exist');
        }
        $categoriesPayload = $this->findCategoriesIdsByCategoryCodes($categoryCodes, $context);

        if (count($categoriesPayload) < count($categoryCodes)) {
            $this->logger->warning(
                sprintf(
                    'Found less categories that assigned to product in Ergonode %d[%d]',
                    count($categoriesPayload),
                    count($categoryCodes)
                )
            );
        }

        $payload = [
            [
                'id' => $productId,
                'categories' => $categoriesPayload
            ]
        ];

        try {
            $writeResult = $this->productRepository->upsert(
                $payload,
                $context
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $writeResult->getPrimaryKeys(ProductDefinition::ENTITY_NAME);
    }

    private function findProductIdBySku(string $sku, Context $context): ?string
    {
        $productIds = $this->productProvider->getProductIdsBySkus([$sku], $context);

        if (\count($productIds) > 0) {
            return array_pop($productIds);
        }

        return null;
    }

    private function findCategoriesIdsByCategoryCodes(array $categoryCodes, Context $context): array
    {
        $result = [];
        $categories = $this->categoryProvider->getCategoryIdsByCodes($categoryCodes, $context);
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category,
            ];
        }
        return $result;
    }
}

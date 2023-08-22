<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\ProductStreamResultsProxy;
use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Factory\ProductDataFactory;
use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Struct\ProductContainer;
use Ergonode\IntegrationShopware\Transformer\ProductTransformerChain;
use Ergonode\IntegrationShopware\Transformer\VariantsTransformer;
use Ergonode\IntegrationShopware\Util\CodeBuilderUtil;
use Ergonode\IntegrationShopware\Util\Constants;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityRepositoryNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Throwable;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_values;
use function is_array;

class ProductPersistor
{
    private EntityRepository $productRepository;

    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    private LoggerInterface $logger;

    private EntityRepository $productCategoryRepository;

    private VariantsTransformer $variantsTransformer;

    private ProductContainer $productContainer;

    private ErgonodeCursorManager $cursorManager;

    private ProductDataFactory $productDataFactory;

    public function __construct(
        EntityRepository $productRepository,
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        LoggerInterface $ergonodeSyncLogger,
        EntityRepository $productCategoryRepository,
        VariantsTransformer $variantsTransformer,
        ProductContainer $productContainer,
        ErgonodeCursorManager $cursorManager,
        ProductDataFactory $productDataFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->logger = $ergonodeSyncLogger;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->variantsTransformer = $variantsTransformer;
        $this->productContainer = $productContainer;
        $this->cursorManager = $cursorManager;
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @returns array Persisted primary keys
     */
    public function persist(array $productListData, Context $context): array
    {
        $this->loadProductContainer($productListData, $context);
        $payloads = [];

        $productListData = $this->filterMainProducts($productListData);

        foreach ($productListData as $productData) {
            $mainProductPayload = $this->getProductPayload($productData['node'] ?? [], $context);
            if (
                empty($mainProductPayload) ||
                false === isset($mainProductPayload['productNumber'])
            ) {
                continue;
            }

            $payloads[$mainProductPayload['productNumber']] = $mainProductPayload;

            $this->logger->info('Processed product.', [
                'sku' => $mainProductPayload['productNumber'],
                'variantSkus' => array_map(
                    fn(array $child) => $child['productNumber'], $mainProductPayload['children'] ?? []
                ),
                'categoryIds' => array_map(
                    fn(array $category) => $category['id'], $mainProductPayload['categories'] ?? []
                ),
            ]);
        }

        $productIds = [];
        foreach ($payloads as $payload) {
            if (array_key_exists('id', $payload)) {
                $productIds[] = $payload['id'];
            }
        }

        if (false === $context->hasState(Constants::STATE_PRODUCT_APPEND_CATEGORIES)) {
            $this->clearProductCategories($productIds, $context);
        }

        $writeResult = $this->productRepository->upsert(
            array_values($payloads),
            $context
        );

        return $writeResult->getPrimaryKeys(ProductDefinition::ENTITY_NAME);
    }

    public function deleteProductIds(array $productIds, Context $context): void
    {
        $this->productRepository->delete(
            array_map(static fn($id) => ['id' => $id], array_values($productIds)),
            $context
        );
    }

    /**
     * @param string[] $productIds
     */
    public function clearProductCategories(array $productIds, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productId', $productIds));
        $ids = $this->productCategoryRepository->searchIds($criteria, $context)->getIds();

        if (false === empty($ids)) {
            $this->productCategoryRepository->delete($ids, $context);
        }
    }

    private function getProductPayload(array $productData, Context $context): array
    {
        if (empty($productData)) {
            return [];
        }

        $sku = $productData['sku'];

        $existingProduct = $this->productContainer->get($sku);

        $isInitialPaginatedImport = $this->checkIsInitialPaginatedImport($productData, $context);

        $dto = $this->productDataFactory->create($productData, $isInitialPaginatedImport);
        $dto->setSwProduct($existingProduct);

        //try {
            $transformedData = $this->productTransformerChain->transform(
                $dto,
                $context
            );

            $transformedData = $this->variantsTransformer->transform($transformedData, $context);
        //} catch (Throwable $e) {
        //    $this->logger->error('Error while transforming product. Product has been omitted.', [
        //        'sku' => $sku,
        //        'message' => $e->getMessage(),
        //        'file' => $e->getFile() . ':' . $e->getLine(),
        //    ]);
        //
        //    return [];
        //}

        throw new \Exception('dtp');
        try {
            $this->deleteEntities($dto, $context);
        } catch (Throwable $e) {
            $this->logger->error('Error while deleting related entities. Product has been omitted.', [
                'sku' => $sku,
                'ids' => $dto->getEntitiesToDelete(),
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            return [];
        }

        return array_filter(
            $transformedData->getShopwareData(),
            fn($value) => !empty($value) || 0 === $value || false === $value || null === $value
        );
    }

    private function deleteEntities(ProductTransformationDTO $dto, Context $context): void
    {
        foreach ($dto->getEntitiesToDelete() as $entityName => $payload) {
            if (!is_array($payload)) {
                continue;
            }

            try {
                $repository = $this->definitionInstanceRegistry->getRepository($entityName);

                $repository->delete(array_values($payload), $context);
            } catch (EntityRepositoryNotFoundException $e) {
                continue;
            }
        }
    }

    private function loadProductContainer(array $productListData, Context $context): void
    {
        $skus = $this->extractSkus($productListData);

        $entities = $this->productProvider->getProductsBySkuList($skus, $context, [
            'media',
            'properties',
            'options',
            'children',
            'configuratorSettings',
            'crossSellings.assignedProducts',
            'crossSellings.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        $this->productContainer->clear();
        foreach ($entities as $productEntity) {
            $this->productContainer->set($productEntity->getProductNumber(), $productEntity);
        }
    }

    /**
     * Removes products that will be handled inside their main products.
     */
    private function filterMainProducts(array $productListData): array
    {
        $variantSkus = $this->extractSkus($productListData, true);

        return array_values(
            array_filter(
                $productListData,
                fn(array $productData) => false === in_array($productData['node']['sku'] ?? null, $variantSkus)
            )
        );
    }

    private function extractSkus(array $productListData, bool $onlyVariants = false): array
    {
        foreach ($productListData as $productData) {
            if (false === $onlyVariants) {
                $skus[] = $productData['node']['sku'];
            }

            foreach ($productData['node']['variantList']['edges'] ?? [] as $variantData) {
                $skus[] = $variantData['node']['sku'];
            }
        }

        return $skus ?? [];
    }

    public function deleteOrphanedSkus(string $sku, Context $context, array $ergonodeData): void
    {
        $product = $this->productProvider->getProductBySku($sku, $context, ['children']);
        if (null === $product) {
            return;
        }

        $ergonodeSkus = array_map(function ($record) {
            return $record['node']['sku'];
        }, $ergonodeData);

        $variantIdsToDelete = [];
        foreach ($product->getChildren() as $variant) {
            if (!in_array($variant->getProductNumber(), $ergonodeSkus)) {
                $variantIdsToDelete[] = $variant->getId();
            }
        }

        $this->deleteProductIds($variantIdsToDelete, $context);
    }

    private function checkIsInitialPaginatedImport(array $productData, Context $context): bool
    {
        if ($productData['__typename'] === 'VariableProduct') {
            $sku = $productData['sku'];
            $variantsCursorKey = CodeBuilderUtil::build(ProductStreamResultsProxy::VARIANT_LIST_FIELD, $sku);
            $variantsCursor = $this->cursorManager->getCursorEntity($variantsCursorKey, $context);

            return $variantsCursor === null;
        }

        return false;
    }
}

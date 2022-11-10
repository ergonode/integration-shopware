<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Exception\MissingRequiredProductMappingException;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Transformer\ProductTransformerChain;
use Ergonode\IntegrationShopware\Transformer\VariantsTransformer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
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
    /**
     * @var ProductEntity[]
     */
    private array $existingProductCache = [];

    /**
     * @var string[]
     */
    private array $existingVariantSkus = [];

    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    private LoggerInterface $logger;

    private EntityRepositoryInterface $productCategoryRepository;

    private VariantsTransformer $variantsTransformer;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        LoggerInterface $ergonodeSyncLogger,
        EntityRepositoryInterface $productCategoryRepository,
        VariantsTransformer $variantsTransformer
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->logger = $ergonodeSyncLogger;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->variantsTransformer = $variantsTransformer;
    }

    /**
     * @throws MissingRequiredProductMappingException
     * @returns array Persisted primary keys
     */
    public function persist(array $productListData, Context $context): array
    {
        $this->loadExistingProductCache($productListData, $context);
        $payloads = [];

        foreach ($productListData as $productData) {
            try {
                $mainProductPayload = $this->getProductPayload($productData['node'], $context);
                if (empty($mainProductPayload)) {
                    continue;
                }

                $payloads[$mainProductPayload['productNumber']] = $mainProductPayload;

                foreach ($mainProductPayload['children'] ?? [] as $childrenPayload) {
                    $payloads = $this->removeVariantsProcessedAsMain($childrenPayload, $payloads);
                }

                $this->logger->info('Processed product.', [
                    'sku' => $mainProductPayload['productNumber'],
                ]);
            } catch (Throwable $e) {
                $sku = isset($productData, $productData['node']['sku']) ? $productData['node']['sku'] : null;
                $this->logger->error('Error while transforming product.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'sku' => $sku,
                ]);
            }
        }

        $productIds = [];
        foreach ($payloads as $payload) {
            if (array_key_exists('id', $payload)) {
                $productIds[] = $payload['id'];
            }
        }

        $this->clearProductCategories($productIds, $context);

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

    /**
     * @throws MissingRequiredProductMappingException
     */
    private function getProductPayload(array $productData, Context $context): array
    {
        $sku = $productData['sku'];
        if ($this->isProductProcessedAsVariant($sku)) {
            return [];
        }

        $existingProduct = $this->existingProductCache[$sku] ?? null;

        $dto = new ProductTransformationDTO($productData);
        $dto->setSwProduct($existingProduct);

        $transformedData = $this->productTransformerChain->transform(
            $dto,
            $context
        );

        $transformedData = $this->variantsTransformer->transform($transformedData, $context);

        $this->deleteEntities($dto, $context);

        return array_filter(
            $transformedData->getShopwareData(),
            fn($value) => !empty($value) || 0 === $value || false === $value
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

    private function loadExistingProductCache(array $productListData, Context $context)
    {
        $skus = [];
        foreach ($productListData as $productData) {
            $skus[] = $productData['node']['sku'];
            foreach ($productData['node']['variantList']['edges'] ?? [] as $variantData) {
                $skus[] = $variantData['node']['sku'];
            }
        }

        $this->existingProductCache = [];
        $entities = $this->productProvider->getProductsBySkuList($skus, $context, [
            'media',
            'properties',
            'options',
            'children',
            'configuratorSettings',
            'crossSellings.assignedProducts',
            'crossSellings.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        foreach ($entities as $productEntity) {
            $this->existingProductCache[$productEntity->getProductNumber()] = $productEntity;
        }
    }

    private function isProductProcessedAsVariant(string $sku): bool
    {
        if (in_array($sku, $this->existingVariantSkus)) {
            return true;
        }

        return false;
    }

    private function removeVariantsProcessedAsMain(array $childrenPayload, array $payloads): array
    {
        if (isset($childrenPayload['productNumber'], $payloads[$childrenPayload['productNumber']])) {
            unset($payloads[$childrenPayload['productNumber']]);
        }

        return $payloads;
    }
}

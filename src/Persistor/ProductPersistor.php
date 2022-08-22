<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Exception\MissingRequiredProductMappingException;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Transformer\ProductTransformerChain;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityRepositoryNotFoundException;
use function array_filter;
use function array_values;
use function is_array;

class ProductPersistor
{
    private array $existingProductCache = [];

    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    private LoggerInterface $logger;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        LoggerInterface $syncLogger
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->logger = $syncLogger;
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
            // how do we know we fetch only variableproducts? 
            if ($productData['node']['__typename'] !== 'VariableProduct') {
                continue;
            }
            try {
                $mainProductPayload = $this->getProductPayload($productData['node'], false, $context);

                foreach ($productData['node']['variantList']['edges'] ?? [] as $variantData) {
                    $mainProductPayload['children'][] = $this->getProductPayload($variantData['node'], true, $context);
                }

                $payloads[] = $mainProductPayload;

                $this->logger->info('Processed product.', [
                    'sku' => $mainProductPayload['productNumber']
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Error while transforming product.', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'sku' => $node['sku'] ?? null,
                ]);
            }
        }

        $writeResult = $this->productRepository->upsert(
            $payloads,
            $context
        );

        return $writeResult->getPrimaryKeys(ProductDefinition::ENTITY_NAME);
    }

    public function deleteProductIds(array $productIds, Context $context): void
    {
        $this->productRepository->delete(
            \array_map(static fn($id) => ['id' => $id], $productIds),
            $context
        );
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    private function getProductPayload(array $productData, bool $isVariant, Context $context): array
    {
        $sku = $productData['sku'];
        $existingProduct = $this->existingProductCache[$sku] ?? null;

        $dto = new ProductTransformationDTO($productData);
        $dto->setIsVariant($isVariant);
        $dto->setSwProduct($existingProduct);

        $transformedData = $this->productTransformerChain->transform(
            $dto,
            $context
        );
        $this->deleteEntities($dto, $context);

        return array_filter($transformedData->getShopwareData());
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
            'crossSellings.assignedProducts',
            'crossSellings.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        foreach ($entities as $productEntity) {
            $this->existingProductCache[$productEntity->getProductNumber()] = $productEntity;
        }
    }
}

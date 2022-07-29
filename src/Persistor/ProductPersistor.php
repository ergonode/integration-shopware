<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Exception\MissingRequiredProductMappingException;
use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Transformer\ProductTransformerChain;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityRepositoryNotFoundException;
use function array_filter;
use function array_merge_recursive;
use function array_values;
use function is_array;

class ProductPersistor
{
    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    private array $existingProductCache = [];

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain,
        DefinitionInstanceRegistry $definitionInstanceRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function persist(array $productListData, Context $context): void
    {
        $this->loadExistingProductCache($productListData, $context);
        $payloads = [];
        foreach ($productListData as $productData) {
            $payloads[] = $this->persistProduct($productData['node'], $context);
        }

        $this->productRepository->upsert(
            $payloads,
            $context
        );
    }

    public function deleteProductIds(array $productIds, Context $context): void
    {
        $this->productRepository->delete(
            \array_map(static fn($id) => ['id' => $id], $productIds),
            $context
        );
    }

    /**
     * @return array Payload
     * @throws MissingRequiredProductMappingException
     */
    protected function persistProduct(array $productData, Context $context): array
    {
        $sku = $productData['sku'];
        $existingProduct = $this->existingProductCache[$sku] ?? null;

        $dto = new ProductTransformationDTO($productData);
        $dto->setIsVariant(false);
        $dto->setSwProduct($existingProduct);

        $transformedData = $this->productTransformerChain->transform(
            $dto,
            $context
        );
        $this->deleteEntities($dto, $context);

        $transformedVariants = [];
        foreach ($productData['variantList']['edges'] ?? [] as $variantData) {
            $variantData = $variantData['node'];
            $existingVariant = $this->existingProductCache[$variantData['sku']] ?? null;
            $dto = new ProductTransformationDTO($variantData);
            $dto->setIsVariant(true);
            $dto->setSwProduct($existingVariant);

            $transformedVariants[] = $this->productTransformerChain->transform(
                $dto,
                $context
            )->getShopwareData();

            $this->deleteEntities($dto, $context);
        }

        $swProductData = array_merge_recursive(
            $transformedData->getShopwareData(),
            [
                'children' => $transformedVariants
            ]
        );

        return array_filter($swProductData);
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
            foreach ($productData['variantList']['edges'] ?? [] as $variantData) {
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
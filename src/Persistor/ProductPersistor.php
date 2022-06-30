<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityRepositoryNotFoundException;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Transformer\ProductTransformerChain;

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
     * @return string Shopware product ID
     * @throws MissingRequiredProductMappingException
     */
    public function persist(array $productData, Context $context): string
    {
        $productId = $this->persistProduct($productData, null, $context);

        foreach ($productData['variantList']['edges'] ?? [] as $variantData) {
            $this->persistProduct($variantData['node'], $productId, $context);
        }

        return $productId;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    protected function persistProduct(array $productData, ?string $parentId, Context $context): string
    {
        $sku = $productData['sku'];
        $existingProduct = $this->productProvider->getProductBySku($sku, $context, ['media']);

        $dto = new ProductTransformationDTO($productData);
        $dto->setSwProduct($existingProduct);

        $transformedData = $this->productTransformerChain->transform(
            $dto,
            $context
        );

        $swProductData = array_merge_recursive(
            $transformedData->getShopwareData(),
            [
                'id' => $dto->isUpdate() ? $existingProduct->getId() : null,
                'parentId' => $parentId,
                'productNumber' => $sku,
            ]
        );

        $swProductData = array_filter($swProductData);

        $writtenProducts = $this->productRepository->upsert(
            [$swProductData],
            $context
        );

        $this->deleteEntities($dto, $context);

        $ids = $writtenProducts->getPrimaryKeys(ProductDefinition::ENTITY_NAME);

        return reset($ids);
    }

    private function deleteEntities(ProductTransformationDTO $dto, Context $context): void
    {
        foreach ($dto->getEntitiesToDelete() as $entityName => $ids) {
            if (!is_array($ids)) {
                continue;
            }

            try {
                $repository = $this->definitionInstanceRegistry->getRepository($entityName);

                $payload = array_values(array_map(fn($id) => ['id' => $id], $ids));
                $repository->delete($payload, $context);
            } catch (EntityRepositoryNotFoundException $e) {
                continue;
            }
        }
    }
}
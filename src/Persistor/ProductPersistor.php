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
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Transformer\ProductTransformerChain;

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
     * @throws MissingRequiredProductMappingException
     */
    public function persist(ProductResultsProxy $results, Context $context): void
    {
        $parentId = $this->persistProduct($results, null, $context);

        // todo variants
//        foreach ($results->getVariants() as $variantData) {
//            $this->persistProduct($variantData['node'], $parentId, $context);
//        }
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    protected function persistProduct(ProductResultsProxy $results, ?string $parentId, Context $context): string
    {
        $productData = $results->getMainData();

        $sku = $productData['sku'];
        $existingProduct = $this->productProvider->getProductBySku($sku, $context, ['media']);

        $dto = new ProductTransformationDTO($results);
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
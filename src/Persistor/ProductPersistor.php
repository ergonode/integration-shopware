<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Transformer\ProductTransformerChain;

class ProductPersistor
{
    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;

    private ProductTransformerChain $productTransformerChain;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ProductProvider $productProvider,
        ProductTransformerChain $productTransformerChain
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformerChain = $productTransformerChain;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function persist(ProductResultsProxy $results, Context $context): array
    {
        $entities = $this->persistProduct($results->getProductData(), null, $context);

        $parentId = $entities[ProductDefinition::ENTITY_NAME][0];

        foreach ($results->getVariants() as $variantData) {
            $entities = array_merge_recursive(
                $entities,
                $this->persistProduct($variantData['node'], $parentId, $context)
            );
        }

        return $entities;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    protected function persistProduct(array $productData, ?string $parentId, Context $context): array
    {
        $transformedData = $this->productTransformerChain->transform($productData, $context);

        $sku = $productData['sku'];
        $existingProduct = $this->productProvider->getProductBySku($sku, $context);

        $swProductData = \array_merge_recursive(
            [
                'id' => null !== $existingProduct ? $existingProduct->getId() : null,
                'parentId' => $parentId,
                'productNumber' => $sku,
            ],
            $transformedData
        );

        $written = $this->productRepository->upsert(
            [$swProductData],
            $context
        );

        return [
            ProductDefinition::ENTITY_NAME => $written->getPrimaryKeys(ProductDefinition::ENTITY_NAME),
        ];
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
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
        $existingProduct = $this->productProvider->getProductBySku($sku, $context);

        $operation = null === $existingProduct ?
            ProductTransformationDTO::OPERATION_CREATE : ProductTransformationDTO::OPERATION_UPDATE;

        $transformedData = $this->productTransformerChain->transform(
            new ProductTransformationDTO($operation, $productData),
            $context
        );

        $swProductData = \array_merge_recursive(
            [
                'id' => ProductTransformationDTO::OPERATION_UPDATE === $operation ? $existingProduct->getId() : null,
                'parentId' => $parentId,
                'productNumber' => $sku,
            ],
            $transformedData->getShopwareData()
        );

        $swProductData = \array_filter($swProductData);

        $productIds = $this->productRepository->upsert(
            [$swProductData],
            $context
        )->getPrimaryKeys(ProductDefinition::ENTITY_NAME);

        return $productIds[0];
    }
}
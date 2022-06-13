<?php

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Api\GqlResponse;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Transformer\ProductTransformer;

class ProductPersistor
{
    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;
    private ProductTransformer $productTransformer;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        ProductProvider $productProvider,
        ProductTransformer $productTransformer
    ) {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
        $this->productTransformer = $productTransformer;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function persist(GqlResponse $response, Context $context): void
    {
        $productData = $response->getData()['product'];
        $parentId = $this->persistProduct($productData, null, $context);

        foreach ($productData['variantList']['edges'] as $variantData) {
            $this->persistProduct($variantData['node'], $parentId, $context);
        }
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    protected function persistProduct(array $productData, ?string $parentId, Context $context): string
    {



        $context = new Context(new SystemSource(), [], Defaults::CURRENCY,['0a229a899a504695aa1970ede337bd19']);



        $transformedData = $this->productTransformer->transform($productData, $context);
        $sku = $productData['sku'];

        //TODO Process taxID
        $taxId = 'f9646f89e4534e64bdce99cedb38afba';

        $existingProduct = $this->productProvider->getProductBySku($sku, $context);

        $swProductData = \array_merge_recursive(
            [
                'id' => null !== $existingProduct ? $existingProduct->getId() : null,
                'parentId' => $parentId,
                'productNumber' => $sku,
                'price' => [
                    'linked' => true,
                    'currencyId' => Defaults::CURRENCY,
                ],
                'taxId' => $taxId,
            ],
            $transformedData
        );

        //TODO generalize
        $swProductData['price'] = [$swProductData['price']];

        $productIds = $this->productRepository->upsert(
            [$swProductData],
            $context
        )->getPrimaryKeys(ProductDefinition::ENTITY_NAME);

        return $productIds[0];
    }
}
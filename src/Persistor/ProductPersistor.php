<?php

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Api\GqlResponse;
use Strix\Ergonode\Provider\ProductProvider;

class ProductPersistor
{
    private EntityRepositoryInterface $productRepository;

    private ProductProvider $productProvider;

    public function __construct(EntityRepositoryInterface $productRepository, ProductProvider $productProvider)
    {
        $this->productRepository = $productRepository;
        $this->productProvider = $productProvider;
    }

    public function persist(GqlResponse $response, Context $context): void
    {
        $productData = $response->getData()['product'];
        $parentId = $this->persistProduct($productData, null, $context);

        foreach ($productData['variantList']['edges'] as $variantData) {
            $this->persistProduct($variantData['node'], $parentId, $context);
        }
    }

    public function persistProduct(array $productData, ?string $parentId, Context $context): string
    {
        // TODO dynamic attribute mapping
        $sku = $productData['sku'];
        $productName = 'Ergonode Test Product';
        $price = 100;
        $priceGross = 111;
        $stock = 999;

        //TODO Process taxID
        $taxId = 'f9646f89e4534e64bdce99cedb38afba';

        $existingProduct = $this->productProvider->getProductBySku($sku, $context);

        $productIds = $this->productRepository->upsert(
            [[
                'id' => null !== $existingProduct ? $existingProduct->getId() : null,
                'parentId' =>$parentId,
                'productNumber' => $sku,
                'name' => $productName,
                'price' => [[
                    'net' => $price,
                    'gross' => $priceGross,
                    'linked' => true,
                    'currencyId' => Defaults::CURRENCY,
                ]],
                'stock' => $stock,
                'taxId' => $taxId
            ]],
            $context
        )->getPrimaryKeys(ProductDefinition::ENTITY_NAME);

        return $productIds[0];
    }
}
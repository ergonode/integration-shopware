<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;
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

    public function persist(ProductResultsProxy $results, Context $context): void
    {
        $parentId = $this->persistProduct($results->getProductData(), null, $context);

        foreach ($results->getVariants() as $variantData) {
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
        $taxId = 'f2994dd722dd4e828614187aa26a9f11';

        $existingProduct = $this->productProvider->getProductBySku($sku, $context);

        $productIds = $this->productRepository->upsert(
            [[
                'id' => null !== $existingProduct ? $existingProduct->getId() : null,
                'parentId' => $parentId,
                'productNumber' => $sku,
                'name' => $productName,
                'price' => [[
                    'net' => $price,
                    'gross' => $priceGross,
                    'linked' => true,
                    'currencyId' => Defaults::CURRENCY,
                ]],
                'stock' => $stock,
                'taxId' => $taxId,
            ]],
            $context
        )->getPrimaryKeys(ProductDefinition::ENTITY_NAME);

        return $productIds[0];
    }
}
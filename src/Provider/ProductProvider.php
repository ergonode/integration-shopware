<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductProvider
{
    private EntityRepositoryInterface $productRepository;

    public function __construct(
        EntityRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function getProductBySku(string $sku, Context $context, array $associations = []): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $sku));
        $criteria->addAssociations($associations);

        return $this->productRepository->search($criteria, $context)->first();
    }

    public function getProductsBySkuList(array $skuList, Context $context, array $associations = []): ProductCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $skuList));
        $criteria->addAssociations($associations);

        return $this->productRepository->search($criteria, $context)->getEntities();
    }

    /**
     * @return string[]
     */
    public function getProductIdsBySkus(array $skus, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $skus));

        return $this->productRepository->searchIds($criteria, $context)->getIds();
    }
}

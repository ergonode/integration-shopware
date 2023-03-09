<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\ProductCrossSelling\ProductCrossSellingExtension;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductCrossSellingProvider
{
    private EntityRepository $productCrossSellingRepository;

    public function __construct(
        EntityRepository $productCrossSellingRepository
    ) {
        $this->productCrossSellingRepository = $productCrossSellingRepository;
    }

    public function getProductCrossSellingByMapping(
        string $productId,
        string $code,
        Context $context,
        array $associations = []
    ): ?ProductCrossSellingEntity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter(AbstractErgonodeMappingExtension::EXTENSION_NAME . '.code', $code));
        $criteria->addFilter(new EqualsFilter(
            AbstractErgonodeMappingExtension::EXTENSION_NAME . '.type',
            ProductCrossSellingExtension::ERGONODE_TYPE
        ));

        $criteria->addAssociation(AbstractErgonodeMappingExtension::EXTENSION_NAME);
        $criteria->addAssociations($associations);

        return $this->productCrossSellingRepository->search($criteria, $context)->first();
    }
}
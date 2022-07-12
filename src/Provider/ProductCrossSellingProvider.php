<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;
use Strix\Ergonode\Extension\ProductCrossSelling\ProductCrossSellingExtension;

class ProductCrossSellingProvider
{
    private EntityRepositoryInterface $productCrossSellingRepository;

    public function __construct(
        EntityRepositoryInterface $productCrossSellingRepository
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
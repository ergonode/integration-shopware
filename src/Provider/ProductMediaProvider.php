<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductMediaProvider
{
    private EntityRepositoryInterface $productMediaRepository;

    public function __construct(
        EntityRepositoryInterface $productMediaRepository
    ) {
        $this->productMediaRepository = $productMediaRepository;
    }

    public function getProductMedia(string $mediaId, string $productId, Context $context): ?ProductMediaEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('mediaId', $mediaId));

        $productMedia = $this->productMediaRepository->search($criteria, $context)->first();

        if ($productMedia instanceof ProductMediaEntity) {
            return $productMedia;
        }

        return null;
    }
}
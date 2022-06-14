<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ErgonodeMappingProvider
{
    private EntityRepositoryInterface $repository;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getIdsByType(array $codes, string $type, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('code', $codes));
        $criteria->addFilter(new EqualsFilter('type', $type));

        return $this->repository->search($criteria, $context)->getIds();
    }
}
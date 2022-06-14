<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Entity\ErgonodeCursor\ErgonodeCursorEntity;

class ErgonodeCursorProvider
{
    private EntityRepositoryInterface $repository;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getEntity(string $query, Context $context): ?ErgonodeCursorEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('query', $query));

        $cursor = $this->repository->search($criteria, $context)->first();

        if ($cursor instanceof ErgonodeCursorEntity) {
            return $cursor;
        }

        return null;
    }

    public function get(string $query, Context $context): ?string
    {
        $cursor = $this->getEntity($query, $context);

        if ($cursor instanceof ErgonodeCursorEntity) {
            return $cursor->getCursor();
        }

        return null;
    }
}
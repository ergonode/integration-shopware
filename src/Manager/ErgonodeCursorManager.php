<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Entity\ErgonodeCursor\ErgonodeCursorEntity;

class ErgonodeCursorManager
{
    private EntityRepositoryInterface $repository;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function persist(string $cursor, string $query, Context $context): EntityWrittenContainerEvent
    {
        $lastCursor = $this->getCursorEntity($query, $context);

        return $this->repository->upsert([
            [
                'id' => $lastCursor ? $lastCursor->getId() : null,
                'cursor' => $cursor,
                'query' => $query,
            ],
        ], $context);
    }

    public function getCursorEntity(string $query, Context $context): ?ErgonodeCursorEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('query', $query));

        $cursor = $this->repository->search($criteria, $context)->first();

        if ($cursor instanceof ErgonodeCursorEntity) {
            return $cursor;
        }

        return null;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Entity\ErgonodeCursor\ErgonodeCursorEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

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

    public function deleteCursor(string $query, Context $context): void
    {
        $cursorEntity = $this->getCursorEntity($query, $context);
        if (null !== $cursorEntity) {
            $this->repository->delete([
                [
                    'id' => $cursorEntity->getId()
                ]
            ], $context);
        }
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

    public function getCursor(string $query, Context $context): ?string
    {
        $cursorEntity = $this->getCursorEntity($query, $context);
        return null === $cursorEntity ? null : $cursorEntity->getCursor();
    }
}
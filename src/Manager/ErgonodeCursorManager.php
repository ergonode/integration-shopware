<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Entity\ErgonodeCursor\ErgonodeCursorCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeCursor\ErgonodeCursorEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class ErgonodeCursorManager
{
    private EntityRepositoryInterface $repository;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function persist(string $cursor, string $queryName, Context $context): EntityWrittenContainerEvent
    {
        $lastCursor = $this->getCursorEntity($queryName, $context);

        return $this->repository->upsert([
            [
                'id' => $lastCursor ? $lastCursor->getId() : null,
                'cursor' => $cursor,
                'query' => $queryName,
            ],
        ], $context);
    }

    public function deleteCursor(string $queryName, Context $context): void
    {
        $this->deleteCursors([$queryName], $context);
    }

    /**
     * @param string[] $queryNames if empty, it will delete all
     */
    public function deleteCursors(array $queryNames, Context $context): void
    {
        $cursorEntities = $this->getCursorEntities($queryNames, $context);

        if (0 === $cursorEntities->count()) {
            return;
        }

        $this->repository->delete(
            array_values(
                $cursorEntities->map(
                    fn(ErgonodeCursorEntity $entity) => ['id' => $entity->getId()]
                )
            ),
            $context
        );
    }

    public function getCursorEntity(string $queryName, Context $context): ?ErgonodeCursorEntity
    {
        return $this->getCursorEntities([$queryName], $context)->first();
    }

    /**
     * @param string[] $queryNames if empty, it will get all
     */
    public function getCursorEntities(array $queryNames, Context $context): ErgonodeCursorCollection
    {
        $criteria = new Criteria();

        if (!empty($queryNames)) {
            $criteria->addFilter(new EqualsAnyFilter('query', $queryNames));
        }

        $result = $this->repository->search($criteria, $context)->getEntities();

        return new ErgonodeCursorCollection($result);
    }

    public function getCursor(string $queryName, Context $context): ?string
    {
        $cursorEntity = $this->getCursorEntity($queryName, $context);

        return null === $cursorEntity ? null : $cursorEntity->getCursor();
    }
}
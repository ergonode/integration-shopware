<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Strix\Ergonode\Provider\ErgonodeCursorProvider;

class ErgonodeCursorPersistor
{
    private ErgonodeCursorProvider $ergonodeCursorProvider;

    private EntityRepositoryInterface $repository;

    public function __construct(
        ErgonodeCursorProvider $ergonodeCursorProvider,
        EntityRepositoryInterface $repository
    ) {
        $this->ergonodeCursorProvider = $ergonodeCursorProvider;
        $this->repository = $repository;
    }

    public function save(string $cursor, string $query, Context $context): EntityWrittenContainerEvent
    {
        $lastCursor = $this->ergonodeCursorProvider->getEntity($query, $context);

        return $this->repository->upsert([
            [
                'id' => $lastCursor ? $lastCursor->getId() : null,
                'cursor' => $cursor,
                'query' => $query,
            ],
        ], $context);
    }
}
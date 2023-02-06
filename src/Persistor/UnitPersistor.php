<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class UnitPersistor
{
    private EntityRepository $unitRepository;

    public function __construct(
        EntityRepository $unitRepository
    ) {
        $this->unitRepository = $unitRepository;
    }

    public function persist(array $payload, Context $context): void
    {
        $this->unitRepository->upsert(
            [
                $payload
            ],
            $context
        );
    }
}

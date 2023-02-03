<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Unit\UnitEntity;

class UnitProvider
{
    private EntityRepository $unitRepository;

    public function __construct(EntityRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function getIdByName(string $unitValue, Context $context): ?UnitEntity
    {
        $criteria = (new Criteria())
            ->addAssociation('unit_translation')
            ->addFilter(new EqualsFilter('name', $unitValue));

        return $this->unitRepository->search($criteria, $context)->first();
    }
}
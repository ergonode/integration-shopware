<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\Unit\UnitEntity;

class UnitProvider
{
    private EntityRepositoryInterface $unitRepository;

    public function __construct(EntityRepositoryInterface $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function getUnitByNames(array $unitValue, Context $context): ?UnitEntity
    {
        $criteria = (new Criteria())
            ->addAssociation('translations')
            ->addFilter(new EqualsAnyFilter('translations.name', $unitValue));

        return $this->unitRepository->search($criteria, $context)->first();
    }
}

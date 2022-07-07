<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\Tax\TaxEntity;

class TaxProvider
{
    private EntityRepositoryInterface $taxRepository;

    public function __construct(
        EntityRepositoryInterface $taxRepository
    ) {
        $this->taxRepository = $taxRepository;
    }

    public function getDefaultTax(Context $context, array $associations = []): ?TaxEntity
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('position'));
        $criteria->addAssociations($associations);

        return $this->taxRepository->search($criteria, $context)->first();
    }
}

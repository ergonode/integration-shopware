<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\Tax\TaxEntity;

class TaxProvider
{
    private EntityRepository $taxRepository;

    public function __construct(EntityRepository $taxRepository, private readonly SystemConfigService $configService)
    {
        $this->taxRepository = $taxRepository;
    }

    public function getDefaultTax(Context $context, array $associations = []): ?TaxEntity
    {
        $defaultTaxId = $this->configService->getString('core.tax.defaultTaxRate');
        $criteria = new Criteria();
        $criteria->addAssociations($associations);

        if ($defaultTaxId) {
            $criteria->addFilter(new EqualsFilter('id', $defaultTaxId));
        } else {
            $criteria->addSorting(new FieldSorting('position'));
        }

        return $this->taxRepository->search($criteria, $context)->first();
    }

    public function getByTaxRate(float $taxRate, Context $context, array $associations = []): ?TaxEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('taxRate', $taxRate));
        $criteria->addSorting(new FieldSorting('position'));
        $criteria->addAssociations($associations);

        return $this->taxRepository->search($criteria, $context)->first();
    }
}

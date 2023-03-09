<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\Locale\LocaleCollection;

class LocaleProvider
{
    private EntityRepository $localeRepository;

    public function __construct(EntityRepository $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    public function getLocalesByIsoCodes(array $isoCodes, Context $context): LocaleCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('code', $isoCodes));
        $criteria->addAssociation('languages');

        $locales = $this->localeRepository->search($criteria, $context)->getEntities();

        if ($locales instanceof LocaleCollection) {
            return $locales;
        }

        return new LocaleCollection();
    }
}
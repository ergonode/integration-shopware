<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class DeliveryTimeProvider
{
    private EntityRepository $deliveryTimeRepository;

    public function __construct(EntityRepository $deliveryTimeRepository)
    {
        $this->deliveryTimeRepository = $deliveryTimeRepository;
    }

    public function getIdByName(string $productDeliveryTime, Context $context): ?string
    {
        $criteria = new  Criteria();
        $criteria->addFilter(new EqualsFilter('name', $productDeliveryTime));
        $deliveryTimeEntity = $this->deliveryTimeRepository->search($criteria, $context)->first();

        return $deliveryTimeEntity ? $deliveryTimeEntity->getId() : null;
    }
}

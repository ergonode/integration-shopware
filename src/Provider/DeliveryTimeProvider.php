<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class DeliveryTimeProvider
{
    private EntityRepositoryInterface $deliveryTimeRepository;

    public function __construct(EntityRepositoryInterface $deliveryTimeRepository)
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

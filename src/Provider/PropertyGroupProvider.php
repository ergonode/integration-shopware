<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;

class PropertyGroupProvider
{
    private EntityRepositoryInterface $propertyGroupRepository;

    public function __construct(
        EntityRepositoryInterface $propertyGroupRepository
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    public function getPropertyGroupByMapping(string $code, Context $context): ?PropertyGroupEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(AbstractErgonodeMappingExtension::EXTENSION_NAME . '.code', $code));
        $criteria->addAssociations([
            AbstractErgonodeMappingExtension::EXTENSION_NAME,
            'options.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        return $this->propertyGroupRepository->search($criteria, $context)->first();
    }
}
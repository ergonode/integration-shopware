<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Extension\AbstractErgonodeMappingExtension;
use Strix\Ergonode\Extension\PropertyGroupOption\PropertyGroupOptionExtension;

class PropertyGroupOptionProvider
{
    private EntityRepositoryInterface $propertyGroupOptionRepository;

    public function __construct(
        EntityRepositoryInterface $propertyGroupOptionRepository
    ) {
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
    }

    public function getOptionsByMappingArray(array $codes, Context $context): PropertyGroupOptionCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter(AbstractErgonodeMappingExtension::EXTENSION_NAME . '.code', $codes));
        $criteria->addFilter(new EqualsFilter(
            AbstractErgonodeMappingExtension::EXTENSION_NAME . '.type',
            PropertyGroupOptionExtension::ERGONODE_TYPE
        ));

        $criteria->addAssociations([
            AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        $entities = $this->propertyGroupOptionRepository->search($criteria, $context)->getEntities();

        if ($entities instanceof PropertyGroupOptionCollection) {
            return $entities;
        }

        return new PropertyGroupOptionCollection();
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroupOption\PropertyGroupOptionExtension;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class PropertyGroupOptionProvider
{
    private EntityRepository $propertyGroupOptionRepository;

    public function __construct(
        EntityRepository $propertyGroupOptionRepository
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

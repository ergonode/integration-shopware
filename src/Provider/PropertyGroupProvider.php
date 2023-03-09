<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Ergonode\IntegrationShopware\Extension\PropertyGroup\PropertyGroupExtension;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class PropertyGroupProvider
{
    private EntityRepository $propertyGroupRepository;

    public function __construct(
        EntityRepository $propertyGroupRepository
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    public function getPropertyGroupByMapping(string $code, Context $context): ?PropertyGroupEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(AbstractErgonodeMappingExtension::EXTENSION_NAME . '.code', $code));
        $criteria->addFilter(new EqualsFilter(
            AbstractErgonodeMappingExtension::EXTENSION_NAME . '.type',
            PropertyGroupExtension::ERGONODE_TYPE
        ));

        $criteria->addAssociations([
            AbstractErgonodeMappingExtension::EXTENSION_NAME,
            'options.' . AbstractErgonodeMappingExtension::EXTENSION_NAME,
        ]);

        return $this->propertyGroupRepository->search($criteria, $context)->first();
    }

    /**
     * @param string[] $codes
     */
    public function getIdsByCodes(array $codes, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter(AbstractErgonodeMappingExtension::EXTENSION_NAME . '.code', $codes));

        return $this->propertyGroupRepository->searchIds($criteria, $context)->getIds();
    }
}
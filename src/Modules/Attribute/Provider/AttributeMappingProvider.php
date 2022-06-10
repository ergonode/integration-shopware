<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;

class AttributeMappingProvider
{
    private EntityRepositoryInterface $repository;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function provideByShopwareKey(string $key, Context $context): ?ErgonodeAttributeMappingEntity
    {
        return $this->getMappingEntities($this->buildCriteria('shopwareKey', $key), $context)->first();
    }

    public function provideByErgonodeKey(string $key, Context $context): ?ErgonodeAttributeMappingCollection
    {
        return $this->getMappingEntities($this->buildCriteria('ergonodeKey', $key), $context);
    }

    private function getMappingEntities(Criteria $criteria, Context $context): ?ErgonodeAttributeMappingCollection
    {
        $result = $this->repository->search($criteria, $context);
        $result = $result->getEntities();

        if ($result instanceof ErgonodeAttributeMappingCollection) {
            return $result;
        }

        return null;
    }

    private function buildCriteria(string $entityKey, string $searchedKey): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter($entityKey, $searchedKey));

        return $criteria;
    }
}
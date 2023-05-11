<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ErgonodeCategoryMappingEntity $entity)
 * @method void set(string $key, ErgonodeCategoryMappingEntity $entity)
 * @method ErgonodeCategoryMappingEntity[] getIterator()
 * @method ErgonodeCategoryMappingEntity[] getElements()
 * @method ErgonodeCategoryMappingEntity|null get(string $key)
 * @method ErgonodeCategoryMappingEntity|null first()
 * @method ErgonodeCategoryMappingEntity|null last()
 */
class ErgonodeCategoryMappingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCategoryMappingEntity::class;
    }
}

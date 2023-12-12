<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ErgonodeCategoryAttributeMappingEntity $entity)
 * @method void set(string $key, ErgonodeCategoryAttributeMappingEntity $entity)
 * @method ErgonodeCategoryAttributeMappingEntity[] getIterator()
 * @method ErgonodeCategoryAttributeMappingEntity[] getElements()
 * @method ErgonodeCategoryAttributeMappingEntity|null get(string $key)
 * @method ErgonodeCategoryAttributeMappingEntity|null first()
 * @method ErgonodeCategoryAttributeMappingEntity|null last()
 */
class ErgonodeCategoryAttributeMappingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCategoryAttributeMappingEntity::class;
    }
}

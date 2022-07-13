<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ErgonodeAttributeMappingEntity $entity)
 * @method void set(string $key, ErgonodeAttributeMappingEntity $entity)
 * @method ErgonodeAttributeMappingEntity[] getIterator()
 * @method ErgonodeAttributeMappingEntity[] getElements()
 * @method ErgonodeAttributeMappingEntity|null get(string $key)
 * @method ErgonodeAttributeMappingEntity|null first()
 * @method ErgonodeAttributeMappingEntity|null last()
 */
class ErgonodeAttributeMappingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeAttributeMappingEntity::class;
    }
}

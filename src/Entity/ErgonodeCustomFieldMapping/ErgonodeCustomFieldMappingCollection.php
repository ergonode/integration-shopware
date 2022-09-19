<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCustomFieldMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ErgonodeCustomFieldMappingEntity $entity)
 * @method void set(string $key, ErgonodeCustomFieldMappingEntity $entity)
 * @method ErgonodeCustomFieldMappingEntity[] getIterator()
 * @method ErgonodeCustomFieldMappingEntity[] getElements()
 * @method ErgonodeCustomFieldMappingEntity|null get(string $key)
 * @method ErgonodeCustomFieldMappingEntity|null first()
 * @method ErgonodeCustomFieldMappingEntity|null last()
 */
class ErgonodeCustomFieldMappingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCustomFieldMappingEntity::class;
    }
}

<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Fixture;

use Ramsey\Uuid\Uuid;
use Strix\Ergonode\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;

class ErgonodeAttributeMappingFixture
{
    public static function entity(string $shopwareKey, string $ergonodeKey): ErgonodeAttributeMappingEntity
    {
        $entity = new ErgonodeAttributeMappingEntity();
        $entity->setUniqueIdentifier(Uuid::uuid4()->toString());
        $entity->setShopwareKey($shopwareKey);
        $entity->setErgonodeKey($ergonodeKey);

        return $entity;
    }

    public static function collection(array $data): ErgonodeAttributeMappingCollection
    {
        foreach ($data as &$entity) {
            $entity = self::entity(...$entity);
        }

        return new ErgonodeAttributeMappingCollection($data);
    }
}
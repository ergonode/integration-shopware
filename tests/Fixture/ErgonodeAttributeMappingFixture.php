<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Fixture;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ramsey\Uuid\Uuid;

class ErgonodeAttributeMappingFixture
{
    public static function entity(string $shopwareKey, string $ergonodeKey): ErgonodeAttributeMappingEntity
    {
        $entity = new ErgonodeAttributeMappingEntity();
        $entity->setUniqueIdentifier(Uuid::uuid4()->toString());
        $entity->setShopwareKey($shopwareKey);
        $entity->setErgonodeKey($ergonodeKey);
        $entity->setCastToBool(false);

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

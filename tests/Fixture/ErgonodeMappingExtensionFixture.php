<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Fixture;

use Ramsey\Uuid\Uuid;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionEntity;
use Strix\Ergonode\Entity\ErgonodeMappingExtension\ErgonodeMappingExtensionCollection;

class ErgonodeMappingExtensionFixture
{
    public static function entity(string $code, string $type): ErgonodeMappingExtensionEntity
    {
        $entity = new ErgonodeMappingExtensionEntity();
        $entity->setUniqueIdentifier(Uuid::uuid4()->toString());
        $entity->setCode($code);
        $entity->setType($type);

        return $entity;
    }

    public static function collection(array $data): ErgonodeMappingExtensionCollection
    {
        foreach ($data as &$entity) {
            $entity = self::entity(...$entity);
        }

        return new ErgonodeMappingExtensionCollection($data);
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping;

class ErgonodeCustomFieldMappingDefinition extends ErgonodeAttributeMappingDefinition
{
    public const ENTITY_NAME = 'ergonode_custom_field_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
}

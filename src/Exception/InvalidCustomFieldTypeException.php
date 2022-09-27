<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Exception;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;

class InvalidCustomFieldTypeException extends InvalidAttributeTypeException
{
    public function __construct(
        ErgonodeAttributeMappingEntity $mapping,
        array $validTypes,
        string $actualType = ''
    ) {
        parent::__construct(
            $mapping,
            $validTypes,
            $actualType,
            'Invalid Custom field type, skipping. Please check Custom field mapping.'
        );
    }
}
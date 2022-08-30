<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Exception;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Exception;

class InvalidAttributeTypeException extends Exception
{
    private ErgonodeAttributeMappingEntity $mapping;

    private array $validTypes;

    private string $actualType;

    public function __construct(ErgonodeAttributeMappingEntity $mapping, array $validTypes, string $actualType = '')
    {
        $this->mapping = $mapping;
        $this->validTypes = $validTypes;
        $this->actualType = $actualType;

        parent::__construct('Invalid Attribute type, skipping. Please check Attribute mapping.');
    }

    public function getMapping(): ErgonodeAttributeMappingEntity
    {
        return $this->mapping;
    }

    public function getValidTypes(): array
    {
        return $this->validTypes;
    }

    public function getValidTypesStr(): string
    {
        return implode('/', $this->validTypes);
    }

    public function getActualType(): string
    {
        return $this->actualType;
    }
}
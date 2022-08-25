<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Exception;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Exception;

class InvalidAttributeTypeException extends Exception
{
    private ErgonodeAttributeMappingEntity $mapping;

    private string $expectedType;

    private string $actualType;

    public function __construct(ErgonodeAttributeMappingEntity $mapping, string $expectedType, string $actualType = '')
    {
        $this->mapping = $mapping;
        $this->expectedType = $expectedType;
        $this->actualType = $actualType;

        parent::__construct('Invalid Attribute type, skipping. Please check Attribute mapping.');
    }

    public function getMapping(): ErgonodeAttributeMappingEntity
    {
        return $this->mapping;
    }

    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    public function getActualType(): string
    {
        return $this->actualType;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Shopware\Core\Content\Property\PropertyGroupEntity;

class PropertyGroupTransformationDTO
{
    private array $ergonodeData;

    private ?PropertyGroupEntity $swPropertyGroup = null;

    private array $propertyGroupPayload = [];

    private array $optionDeletePayload = [];

    public function __construct(array $ergonodeData)
    {
        $this->ergonodeData = $ergonodeData;
    }

    public function getErgonodeData(): array
    {
        return $this->ergonodeData;
    }

    public function setErgonodeData(array $ergonodeData): void
    {
        $this->ergonodeData = $ergonodeData;
    }

    public function getSwPropertyGroup(): ?PropertyGroupEntity
    {
        return $this->swPropertyGroup;
    }

    public function setSwPropertyGroup(?PropertyGroupEntity $swPropertyGroup): void
    {
        $this->swPropertyGroup = $swPropertyGroup;
    }

    public function getPropertyGroupPayload(): array
    {
        return $this->propertyGroupPayload;
    }

    public function setPropertyGroupPayload(array $propertyGroupPayload): void
    {
        $this->propertyGroupPayload = $propertyGroupPayload;
    }

    public function getOptionDeletePayload(): array
    {
        return $this->optionDeletePayload;
    }

    public function setOptionDeletePayload(array $optionDeletePayload): void
    {
        $this->optionDeletePayload = $optionDeletePayload;
    }

    public function setDeletePayloadIds(array $ids): void
    {
        $this->setOptionDeletePayload(
            array_map(fn(string $id) => [
                'id' => $id,
            ], $ids)
        );
    }
}
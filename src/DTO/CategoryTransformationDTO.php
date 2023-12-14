<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

class CategoryTransformationDTO
{

    private array $shopwareData;

    private array $entitiesToDelete = [];


    public function __construct(
        private string $shopwareCategoryId,
        private array $ergonodeCategoryData
    ) {
        $this->shopwareData = [
            'id' => $shopwareCategoryId,
        ];
    }

    public function getShopwareCategoryId(): string
    {
        return $this->shopwareCategoryId;
    }

    public function getErgonodeCategoryData(): array
    {
        return $this->ergonodeCategoryData;
    }

    public function getShopwareData(): array
    {
        return $this->shopwareData;
    }

    public function setShopwareData(array $shopwareData): void
    {
        $this->shopwareData = $shopwareData;
    }

    /**
     * @param array $payload [ 'id' => 'entityId' ] OR [ [ 'id' => 'entityId1' ], [ 'id' => 'entityId2' ] ]
     */
    public function addEntitiesToDelete(string $entityName, array $payload): void
    {
        if (empty($payload)) {
            return;
        }

        if (!isset($this->entitiesToDelete[$entityName])) {
            $this->entitiesToDelete[$entityName] = [];
        }

        foreach ($payload as $payloadPart) {
            if (!is_array($payloadPart)) {
                $this->entitiesToDelete[$entityName][] = $payload;

                return;
            }

            $this->addEntitiesToDelete($entityName, $payloadPart);
        }
    }

    public function getEntitiesToDelete(): array
    {
        return $this->entitiesToDelete;
    }

    public function unsetSwData(string $fieldKey): void
    {
        unset($this->shopwareData[$fieldKey]);
    }
}

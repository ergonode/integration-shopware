<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Shopware\Core\Content\Product\ProductEntity;

class ProductTransformationDTO
{
    private array $ergonodeData;

    private array $shopwareData;

    private ?ProductEntity $swProduct = null;

    private array $entitiesToDelete = [];

    private array $bindingCodes = [];

    public function __construct(array $ergonodeData, array $shopwareData = [])
    {
        $this->ergonodeData = $ergonodeData;
        $this->shopwareData = $shopwareData;
    }

    public function getErgonodeData(): array
    {
        return $this->ergonodeData;
    }

    public function setErgonodeData(array $ergonodeData): void
    {
        $this->ergonodeData = $ergonodeData;
    }

    public function getShopwareData(): array
    {
        return $this->shopwareData;
    }

    public function setShopwareData(array $shopwareData): void
    {
        $this->shopwareData = $shopwareData;
    }

    public function getSwProduct(): ?ProductEntity
    {
        return $this->swProduct;
    }

    public function setSwProduct(?ProductEntity $swProduct): void
    {
        $this->swProduct = $swProduct;
    }

    public function getBindingCodes(): array
    {
        return $this->bindingCodes;
    }

    public function setBindingCodes(array $bindingCodes): void
    {
        $this->bindingCodes = $bindingCodes;
    }

    public function isVariant(): bool
    {
        return false === empty($this->bindingCodes);
    }

    public function isUpdate(): bool
    {
        return null !== $this->swProduct;
    }

    public function isCreate(): bool
    {
        return null === $this->swProduct;
    }

    public function getEntitiesToDelete(): array
    {
        return $this->entitiesToDelete;
    }

    public function addEntitiesToDelete(string $entityName, array $payload): void
    {
        if (empty($payload)) {
            return;
        }

        if (!isset($this->entitiesToDelete[$entityName])) {
            $this->entitiesToDelete[$entityName] = [];
        }

        $this->entitiesToDelete[$entityName] = array_merge_recursive(
            $this->entitiesToDelete[$entityName],
            $payload
        );
    }

    public function unsetSwData(string $fieldKey): void
    {
        unset($this->shopwareData[$fieldKey]);
    }
}
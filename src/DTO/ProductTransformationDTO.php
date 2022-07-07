<?php

declare(strict_types=1);

namespace Strix\Ergonode\DTO;

use Shopware\Core\Content\Product\ProductEntity;

class ProductTransformationDTO
{
    private array $ergonodeData;

    private array $shopwareData;

    private ?ProductEntity $swProduct;

    private array $entitiesToDelete = [];

    private bool $isVariant = false;

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

    public function isVariant(): bool
    {
        return $this->isVariant;
    }

    public function setIsVariant(bool $isVariant): void
    {
        $this->isVariant = $isVariant;
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
        if (!isset($this->entitiesToDelete[$entityName])) {
            $this->entitiesToDelete[$entityName] = [];
        }

        $this->entitiesToDelete[$entityName] = array_merge(
            $this->entitiesToDelete[$entityName],
            $payload
        );
    }

    public function unsetSwData(string $fieldKey): void
    {
        unset($this->shopwareData[$fieldKey]);
    }
}
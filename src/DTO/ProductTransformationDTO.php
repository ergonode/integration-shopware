<?php

declare(strict_types=1);

namespace Strix\Ergonode\DTO;

use Shopware\Core\Content\Product\ProductEntity;
use Strix\Ergonode\Modules\Product\Api\ProductResultsProxy;

class ProductTransformationDTO
{
    private ProductResultsProxy $ergonodeObject;

    private array $shopwareData;

    private ?ProductEntity $swProduct;

    private array $entitiesToDelete = [];

    public function __construct(ProductResultsProxy $ergonodeObject, array $shopwareData = [])
    {
        $this->ergonodeObject = $ergonodeObject;
        $this->shopwareData = $shopwareData;
    }

    public function getErgonodeData(): array
    {
        return $this->ergonodeObject->getMainData();
    }

    public function getErgonodeObject(): ProductResultsProxy
    {
        return $this->ergonodeObject;
    }

    public function setErgonodeObject(ProductResultsProxy $ergonodeObject): void
    {
        $this->ergonodeObject = $ergonodeObject;
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

    public function addEntitiesToDelete(string $entityName, array $ids): void
    {
        if (!isset($this->entitiesToDelete[$entityName])) {
            $this->entitiesToDelete[$entityName] = [];
        }

        $this->entitiesToDelete[$entityName] = array_merge(
            $this->entitiesToDelete[$entityName],
            $ids
        );
    }

    public function unsetSwData(string $fieldKey): void
    {
        unset($this->shopwareData[$fieldKey]);
    }
}
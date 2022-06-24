<?php

declare(strict_types=1);

namespace Strix\Ergonode\DTO;

use Shopware\Core\Content\Product\ProductEntity;
use Strix\Ergonode\Util\Constants;

class ProductTransformationDTO
{
    private array $ergonodeData;

    private array $shopwareData;

    private ?ProductEntity $swProduct;

    private array $entitiesToDelete = [];

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
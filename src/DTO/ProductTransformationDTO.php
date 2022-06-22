<?php

declare(strict_types=1);

namespace Strix\Ergonode\DTO;

class ProductTransformationDTO
{
    private array $ergonodeData;

    private array $shopwareData;

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
}
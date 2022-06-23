<?php

declare(strict_types=1);

namespace Strix\Ergonode\DTO;

class ProductTransformationDTO
{
    public const OPERATION_CREATE = 'create';
    public const OPERATION_UPDATE = 'update';

    private array $ergonodeData;

    private array $shopwareData;

    private string $operation;

    public function __construct(string $operation, array $ergonodeData, array $shopwareData = [])
    {
        $this->operation = $operation;
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

    public function getOperation(): string
    {
        return $this->operation;
    }
}
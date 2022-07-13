<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

class ProductVisibilityDTO
{
    private string $sku;

    private array $salesChannelIds;

    private array $createPayload = [];

    private array $deletePayload = [];

    public function __construct(string $sku, array $salesChannelIds = [])
    {
        $this->sku = $sku;
        $this->salesChannelIds = $salesChannelIds;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    public function getSalesChannelIds(): array
    {
        return $this->salesChannelIds;
    }

    public function setSalesChannelIds(array $salesChannelIds): void
    {
        $this->salesChannelIds = $salesChannelIds;
    }

    public function getCreatePayload(): array
    {
        return $this->createPayload;
    }

    public function setCreatePayload(array $createPayload): void
    {
        $this->createPayload = $createPayload;
    }

    public function getDeletePayload(): array
    {
        return $this->deletePayload;
    }

    public function setDeletePayload(array $deletePayload): void
    {
        $this->deletePayload = $deletePayload;
    }

    public function setDeletePayloadIds(array $ids): void
    {
        $this->setDeletePayload(
            array_map(fn(string $id) => [
                'id' => $id,
            ], $ids)
        );
    }
}
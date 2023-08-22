<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

class ProductShopwareData
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getMinPurchase(): ?int
    {
        return $this->data['minPurchase'] ?? null;
    }

    public function getMaxPurchase(): ?int
    {
        return $this->data['maxPurchase'] ?? null;
    }

    public function setMinPurchase(?int $minPurchase): void
    {
        if (is_null($minPurchase)) {
            unset($this->data['minPurchase']);
        } else {
            $this->data['minPurchase'] = $minPurchase;
        }
    }

    public function setMaxPurchase(?int $maxPurchase): void
    {
        if (is_null($maxPurchase)) {
            unset($this->data['maxPurchase']);
        } else {
            $this->data['maxPurchase'] = $maxPurchase;
        }
    }

    public function setUnitId(?string $id): void
    {
        if (is_null($id)) {
            unset($this->data['unitId']);
        } else {
            $this->data['unitId'] = $id;
        }
    }

    public function resetScaleUnit(): void
    {
        unset($this->data['scaleUnit']);
    }

    public function resetDeliveryTime(): void
    {
        unset($this->data['deliveryTime']);
    }

    public function resetManufacturer(): void
    {
        unset($this->data['manufacturer']);
    }

    public function setDeliveryTimeId(?string $id): void
    {
        if (is_null($id)) {
            unset($this->data['deliveryTimeId']);
        } else {
            $this->data['deliveryTimeId'] = $id;
        }
    }

    public function setManufacturerId(?string $id): void
    {
        if (is_null($id)) {
            unset($this->data['manufacturerId']);
        } else {
            $this->data['manufacturerId'] = $id;
        }
    }

    public function setCrossSellings(array $crossSellings): void
    {
        if (empty($crossSellings)) {
            unset($this->data['crossSellings']);
        } else {
            $this->data['crossSellings'] = $crossSellings;
        }
    }

    public function getCrossSellings(): ?array
    {
        return $this->data['crossSellings'] ?? null;
    }

    public function getProperties(): array
    {
        return $this->data['properties'] ?? [];
    }

    public function setProperties(array $properties): void
    {
        if (empty($properties)) {
            unset($this->data['properties']);
        } else {
            $this->data['properties'] = $properties;
        }
    }

    public function setOptions(array $options)
    {
        if (empty($options)) {
            unset($this->data['options']);
        } else {
            $this->data['options'] = $options;
        }
    }

    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}

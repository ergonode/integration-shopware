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

    public function getStock(): ?string
    {
        return $this->data['stock'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function setMinPurchase(?int $minPurchase): void
    {
        $this->data['minPurchase'] = $minPurchase;
    }

    public function setMaxPurchase(?int $maxPurchase): void
    {
        $this->data['maxPurchase'] = $maxPurchase;
    }

    public function setUnitId(?string $id): void
    {
        $this->data['unitId'] = $id;
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
        $this->data['deliveryTimeId'] = $id;
    }

    public function setManufacturerId(?string $id): void
    {
        $this->data['manufacturerId'] = $id;
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

    public function getAllData(): mixed
    {
        return $this->data;
    }

    public function resetCustomFields(): void
    {
        foreach ($this->data['translations'] as &$translation) {
            if (isset($translation['customFields'])) {
                unset($translation['customFields']);
            }
        }
    }

    public function setCover(array $payload): void
    {
        $this->data['cover'] = $payload;
    }

    public function setCustomFields(array $customFields)
    {
        $this->data['translations'] = array_merge_recursive(
            $this->data['translations'] ?? [],
            $customFields
        );
    }

    public function setMedia(array $payloads): void
    {
        $this->data['media'] = $payloads;
    }

    public function getMedia(): array
    {
        return $this->data['media'] ?? [];
    }

    public function resetTax(): void
    {
        unset($this->data['tax']);
    }

    public function setTax(string $taxId): void
    {
        $this->data['taxId'] = $taxId;
    }

    public function setName(string $name): void
    {
        $this->data['name'] = $name;
    }


    public function setTranslatedName(string $language, string $name): void
    {
        $this->data['translations'][$language]['name'] = $name;
    }

    public function setStock(int $stock): void
    {
        $this->data['stock'] = $stock;
    }

    public function setData(string $key, array $payload): void
    {
        $this->data[$key] = $payload;
    }

    public function setProductNumber(string $sku): void
    {
        $this->data['productNumber'] = $sku;
    }

    public function setPrice(array $pricePayload): void
    {
        $this->data['price'] = $pricePayload;
    }

    public function getPrice(): array
    {
        return $this->data['price'] ?? [];
    }

    public function setId(string $id): void
    {
        $this->data['id'] = $id;
    }
}
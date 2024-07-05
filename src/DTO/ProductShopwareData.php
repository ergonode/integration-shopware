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

    public function setProperties(array $properties): void
    {
        if (empty($properties)) {
            unset($this->data['properties']);
        } else {
            $this->data['properties'] = $properties;
        }
    }

    public function getProperties(): array
    {
        return $this->data['properties'] ?? [];
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

    public function getMedia(): array
    {
        return $this->data['media'] ?? [];
    }

    public function setMedia(array $payloads): void
    {
        $this->data['media'] = $payloads;
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

    public function setTranslatedField(string $field, string $language, mixed $value): void
    {
        $this->data['translations'][$language][$field] = $value;
    }

    public function getTranslatedField(string $field, string $language): mixed
    {
        return $this->data['translations'][$language][$field] ?? null;
    }

    public function getTranslations(): array
    {
        return $this->data['translations'] ?? [];
    }

    public function setStock(int $stock): void
    {
        $this->data['stock'] = $stock;
    }

    public function setData(string $key, mixed $payload): void
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

    public function setId(string $id): void
    {
        $this->data['id'] = $id;
    }

    public function setParentId(string $id): void
    {
        $this->data['parentId'] = $id;
    }

    public function addChild(ProductShopwareData $productShopwareData): void
    {
        $this->data['children'][] = $productShopwareData->getAllData();
    }

    public function setDisplayParent(bool $display = true): void
    {
        $this->data['displayParent'] = $display;
    }

    public function addConfigrationSettings(array $configurationSettings): void
    {
        $this->data['configuratorSettings'][] = $configurationSettings;
    }

    public function setCategories(array $categories): void
    {
        $this->data['categories'] = $categories;
    }

    public function setCmsPageId(string $cmsPageId): void
    {
        $this->data['cmsPageId'] = $cmsPageId;
    }
}

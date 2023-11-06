<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductGalleryAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;

class ProductErgonodeData
{
    private array $mappings;

    private array $attributes = [];

    /**
     * @var ProductErgonodeData[]
     */
    private array $variants = [];

    private array $categories = [];

    private array $bindings = [];

    public function __construct(
        private readonly string $sku,
        private readonly string $type,
        array $mappings
    ) {
        $this->mappings = $mappings;
    }

    private function getMappingKey(string $shopwareKey): ?string
    {
        return $this->mappings[$shopwareKey] ?? null;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function getMinPurchase(): null|ProductAttribute|false
    {
        $key = $this->getMappingKey('minPurchase');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getMaxPurchase(): null|ProductAttribute|false
    {
        $key = $this->getMappingKey('maxPurchase');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getScaleUnit(): ProductSelectAttribute|false|null
    {
        $key = $this->getMappingKey('scaleUnit');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getTax(): ProductAttribute|null|false
    {
        $key = $this->getMappingKey('tax.rate');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getPriceGross(): ProductAttribute|null|false
    {
        $key = $this->getMappingKey('price.gross');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getPriceNet(): ProductAttribute|null|false
    {
        $key = $this->getMappingKey('price.net');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getDeliveryTime(): ProductSelectAttribute|null|false
    {
        $key = $this->getMappingKey('deliveryTime');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getManufacturer(): ProductSelectAttribute|null|false
    {
        $key = $this->getMappingKey('manufacturer');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    public function getMedia(): ProductGalleryAttribute|null|false
    {
        $key = $this->getMappingKey('media');
        if (!$key) {
            return false;
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getAttributeByCode(string $code): mixed
    {
        return $this->attributes[$code] ?? null;
    }

    public function addAttribute(ProductAttribute $attribute): void
    {
        $this->attributes[$attribute->getCode()] = $attribute;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string[] $types
     * @return ProductAttribute[]
     */
    public function getAttributesByTypes(array $types): array
    {
        return array_filter(
            $this->attributes,
            fn(ProductAttribute $attribute) => in_array($attribute->getType(), $types)
        );
    }

    /**
     * @param string[] $codes
     * @return ProductAttribute[]
     */
    public function getAttributesByCodes(array $codes): array
    {
        return array_filter(
            $this->attributes,
            fn(ProductAttribute $attribute) => in_array($attribute->getCode(), $codes)
        );
    }

    /**
     * @return ProductAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getStock(): ?ProductAttribute
    {
        $key = $this->getMappingKey('stock');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getName(): ?ProductAttribute
    {
        $key = $this->getMappingKey('name');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getVariants(): array
    {
        return $this->variants;
    }

    public function addVariant(ProductErgonodeData $variant): void
    {
        $this->variants[] = $variant;
    }

    public function addCategory(array $categoryData): void
    {
        $this->categories[] = $categoryData;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function addBinding(string $code): void
    {
        $this->bindings[] = $code;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}

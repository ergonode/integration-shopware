<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductGalleryAttribute;

class ProductErgonodeData
{
    private array $mappings;

    private array $attributes = [];

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

    public function getMinPurchase(): ?int
    {
        $key = $this->getMappingKey('minPurchase');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getMaxPurchase(): ?int
    {
        $key = $this->getMappingKey('maxPurchase');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getScaleUnit(): ?string
    {
        $key = $this->getMappingKey('scaleUnit');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getTax(): ?string
    {
        $key = $this->getMappingKey('tax');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getDeliveryTime(): ?string
    {
        $key = $this->getMappingKey('deliveryTime');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getManufacturer(): ?string
    {
        $key = $this->getMappingKey('manufacturer');

        return $key ? ($this->attributes[$key] ?? null) : null;
    }

    public function getMedia(): ?ProductGalleryAttribute
    {
        $key = $this->getMappingKey('media');

        return $key ? ($this->attributes[$key] ?? null) : null;
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
     * @param string[] $codes
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
}

<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductSelectAttribute extends ProductAttribute
{
    /**
     * @var ProductAttributeOption[]
     */
    private array $options = [];

    public function addOption(ProductAttributeOption $option)
    {
        $this->options[$option->getCode()] = $option;
    }

    /**
     * @return ProductAttributeOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function clearOptions(): void
    {
        $this->options = [];
    }

    public function hasOption(string $code): bool
    {
        return isset($this->options[$code]);
    }

    public function getFirstOption(): ?ProductAttributeOption
    {
        $firstKey = array_key_first($this->options);
        return $this->options[$firstKey] ?? null;
    }
}

<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductSimpleAttributeTranslation
{
    public function __construct(
        private mixed $value,
        private readonly string $language
    ) {

    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}

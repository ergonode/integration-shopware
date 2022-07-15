<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Struct;

class ErgonodeCategory extends AbstractErgonodeEntity
{
    /**
     * @var ErgonodeTranslatedValue[]
     */
    private array $nameTranslations = [];

    private ?self $parentCategory = null;

    public function getNameTranslations(): array
    {
        return $this->nameTranslations;
    }

    public function getNameByLocale(string $locale): ?ErgonodeTranslatedValue
    {
        return $this->nameTranslations[$locale] ?? null;
    }

    public function addNameTranslation(ErgonodeTranslatedValue $translatedValue): void
    {
        $this->nameTranslations[$translatedValue->getLocale()] = $translatedValue;
    }

    public function setNameTranslation(string $locale, ?string $name = null): void
    {
        $this->nameTranslations[$locale] = new ErgonodeTranslatedValue($locale, $name);
    }

    public function getParentCategory(): ?self
    {
        return $this->parentCategory;
    }

    public function setParentCategory(?self $parentCategory): void
    {
        $this->parentCategory = $parentCategory;
    }
}

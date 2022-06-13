<?php

declare(strict_types=1);

namespace Strix\Ergonode\Struct;

/**
 * @deprecated ?
 */
class ErgonodeTranslatedValue
{
    private string $locale;

    private ?string $value;

    public function __construct(string $locale, ?string $value = null)
    {
        $this->locale = $locale;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}
<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

use Ergonode\IntegrationShopware\Util\IsoCodeConverter;

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

    public function getLanguage(?bool $useShopwareFormat = false): string
    {
        return $useShopwareFormat ? IsoCodeConverter::ergonodeToShopwareIso($this->language) : $this->language;
    }
}

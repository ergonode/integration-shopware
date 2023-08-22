<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductGalleryMultimedia
{
    /** @var ProductSimpleAttributeTranslation[] */
    private array $translations = [];

    public function __construct(private readonly string $filename)
    {
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(ProductSimpleAttributeTranslation $translation): void
    {
        $this->translations[$translation->language] = $translation;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}

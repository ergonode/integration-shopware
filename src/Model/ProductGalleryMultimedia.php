<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductGalleryMultimedia
{
    /** @var ProductMultimediaTranslation[] */
    private array $translations = [];

    public function __construct(private readonly string $filename)
    {
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function addTranslation(ProductMultimediaTranslation $translation): void
    {
        $this->translations[$translation->getLanguage()] = $translation;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}

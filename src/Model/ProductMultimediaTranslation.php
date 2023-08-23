<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductMultimediaTranslation
{
    public function __construct(
        private readonly string $name,
        private readonly string $extension,
        private readonly string $mime,
        private readonly int $size,
        private readonly string $url,
        private readonly string $language
    ) {

    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}

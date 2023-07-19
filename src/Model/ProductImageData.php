<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductImageData
{
    private string $name;

    private string $extension;

    private string $mime;

    private int $size;

    private string $url;

    public function __construct(string $name, string $extension, string $mime, int $size, string $url)
    {
        $this->name = $name;
        $this->extension = $extension;
        $this->mime = $mime;
        $this->size = $size;
        $this->url = $url;
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

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'extension' => $this->getExtension(),
            'size' => $this->getSize(),
            'url' => $this->getUrl(),
            'mime' => $this->getMime(),
        ];
    }
}

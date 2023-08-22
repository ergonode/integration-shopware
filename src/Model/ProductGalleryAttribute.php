<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductGalleryAttribute extends ProductAttribute
{
    /** @var ProductGalleryMultimedia[] */
    private array $multimedia = [];

    public function getAllMultimedia(): array
    {
        return $this->multimedia;
    }

    public function addMultimedia(ProductGalleryMultimedia $multimedia): void
    {
        $this->multimedia[$multimedia->getFilename()] = $multimedia;
    }

    public function getMultimedia(string $filename): ?ProductGalleryMultimedia
    {
        return $this->multimedia[$filename] ?? null;
    }
}

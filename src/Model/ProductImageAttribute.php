<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductImageAttribute extends ProductAttribute
{
    private ?ProductGalleryMultimedia $multimedia = null;

    public function addMultimedia(ProductGalleryMultimedia $multimedia): void
    {
        $this->multimedia = $multimedia;
    }

    public function getMultimedia(): ?ProductGalleryMultimedia
    {
        return $this->multimedia;
    }
}

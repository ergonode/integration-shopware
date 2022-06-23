<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Manager\FileManager;
use Strix\Ergonode\Provider\ProductMediaProvider;
use Strix\Ergonode\Util\Constants;

class ProductMediaTransformer implements ProductDataTransformerInterface
{
    private FileManager $fileManager;

    private ProductMediaProvider $productMediaProvider;

    public function __construct(
        FileManager $fileManager,
        ProductMediaProvider $productMediaProvider
    ) {
        $this->fileManager = $fileManager;
        $this->productMediaProvider = $productMediaProvider;
    }

    public function transform(array $productData, Context $context): array
    {
        if (
            empty($productData[Constants::SW_PRODUCT_FIELD_MEDIA]) ||
            !is_array($productData[Constants::SW_PRODUCT_FIELD_MEDIA])
        ) {
            return $productData;
        }

        foreach ($productData[Constants::SW_PRODUCT_FIELD_MEDIA] as &$image) {
            $mediaId = $this->fileManager->persist($image, $context);
            if (null === $mediaId) {
                continue;
            }

            $image = $this->buildProductMediaPayload($mediaId, $productData, $context);
        }

        return $productData;
    }

    private function buildProductMediaPayload(string $mediaId, array $productData, Context $context): array
    {
        if (empty($productData['id'])) {
            return [];
        }

        $productMedia = $this->productMediaProvider->getProductMedia($mediaId, $productData['id'], $context);

        return [
            'id' => $productMedia ? $productMedia->getId() : null,
            'mediaId' => $mediaId,
        ];
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Strix\Ergonode\DTO\ProductTransformationDTO;
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

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();

        if (
            empty($swData[Constants::SW_PRODUCT_FIELD_MEDIA]) ||
            !is_array($swData[Constants::SW_PRODUCT_FIELD_MEDIA])
        ) {
            $productData->unsetSwData(Constants::SW_PRODUCT_FIELD_MEDIA);
            return $productData;
        }

        foreach ($swData[Constants::SW_PRODUCT_FIELD_MEDIA] as $index => &$image) {
            $mediaId = $this->fileManager->persist($image, $context);
            if (null === $mediaId) {
                unset($swData[Constants::SW_PRODUCT_FIELD_MEDIA][$index]);
                continue;
            }

            $payload = $this->buildProductMediaPayload($mediaId, $productData, $context);
            if (empty($payload)) {
                unset($swData[Constants::SW_PRODUCT_FIELD_MEDIA][$index]);
                continue;
            }

            $image = $payload;

            if (0 === $index) {
                $swData['cover'] = $payload;
            }
        }

        $productData->setShopwareData($swData);

        $idsToDelete = $this->getProductMediaToDelete($productData);
        if (!empty($idsToDelete)) {
            $productData->addEntitiesToDelete(
                ProductMediaDefinition::ENTITY_NAME,
                $idsToDelete
            );
        }

        return $productData;
    }

    private function buildProductMediaPayload(string $mediaId, ProductTransformationDTO $productData, Context $context): array
    {
        if (null === $productData->getSwProduct()) {
            return [];
        }

        $productMedia = $this->productMediaProvider->getProductMedia(
            $mediaId,
            $productData->getSwProduct()->getId(),
            $context
        );

        return [
            'id' => $productMedia ? $productMedia->getId() : Uuid::randomHex(), // need to generate uuid here so media won't be duplicated if used as cover
            'mediaId' => $mediaId,
        ];
    }

    private function getProductMediaToDelete(ProductTransformationDTO $productData): array
    {
        if (null === $productData->getSwProduct()) {
            return [];
        }

        $productMedia = $productData->getSwProduct()->getMedia();
        if (null === $productMedia) {
            return [];
        }

        $swData = $productData->getShopwareData();
        $newProductMedia = $swData[Constants::SW_PRODUCT_FIELD_MEDIA];
        $productMediaIds = $productMedia->getIds();

        if (empty($newProductMedia)) {
            return $productMediaIds;
        }

        $newProductMediaIds = array_filter(
            array_map(fn(array $media) => $media['id'] ?? null, $newProductMedia)
        );

        return array_diff($productMediaIds, $newProductMediaIds);
    }
}
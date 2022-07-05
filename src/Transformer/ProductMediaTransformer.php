<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Manager\FileManager;
use Strix\Ergonode\Provider\ProductMediaProvider;

use function array_diff;
use function array_filter;
use function array_map;
use function is_array;

class ProductMediaTransformer implements ProductDataTransformerInterface
{
    private const SW_PRODUCT_FIELD_MEDIA = 'media';

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
            empty($swData[self::SW_PRODUCT_FIELD_MEDIA]) ||
            !is_array($swData[self::SW_PRODUCT_FIELD_MEDIA])
        ) {
            $productData->unsetSwData(self::SW_PRODUCT_FIELD_MEDIA);

            return $productData;
        }

        foreach ($swData[self::SW_PRODUCT_FIELD_MEDIA] as $index => &$image) {
            $mediaId = $this->fileManager->persist($image, $context);
            if (null === $mediaId) {
                unset($swData[self::SW_PRODUCT_FIELD_MEDIA][$index]);
                continue;
            }

            $payload = $this->buildProductMediaPayload($mediaId, $productData, $context);
            if (empty($payload)) {
                unset($swData[self::SW_PRODUCT_FIELD_MEDIA][$index]);
                continue;
            }

            $image = $payload;

            if (0 === $index) {
                $swData['cover'] = $payload;
            }
        }

        if (empty($swData[self::SW_PRODUCT_FIELD_MEDIA])) {
            unset($swData[self::SW_PRODUCT_FIELD_MEDIA]);
        }

        $productData->setShopwareData($swData);

        $toDelete = $this->getProductMediaDeletePayload($productData);
        if (!empty($toDelete)) {
            $productData->addEntitiesToDelete(
                ProductMediaDefinition::ENTITY_NAME,
                $toDelete
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

    private function getProductMediaDeletePayload(ProductTransformationDTO $productData): array
    {
        if (null === $productData->getSwProduct()) {
            return [];
        }

        $productMedia = $productData->getSwProduct()->getMedia();
        if (null === $productMedia) {
            return [];
        }

        $swData = $productData->getShopwareData();
        $productMediaIds = $productMedia->getIds();

        if (!isset($swData[self::SW_PRODUCT_FIELD_MEDIA])) {
            return array_map(fn(string $id) => ['id' => $id], $productMediaIds);
        }

        $newProductMediaIds = array_filter(
            array_map(fn(array $media) => $media['id'] ?? null, $swData[self::SW_PRODUCT_FIELD_MEDIA])
        );

        $idsToDelete = array_diff($productMediaIds, $newProductMediaIds);

        return array_map(fn(string $id) => ['id' => $id], $idsToDelete);
    }
}
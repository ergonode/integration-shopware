<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Manager\FileManager;
use Ergonode\IntegrationShopware\Provider\ProductMediaProvider;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

use function array_diff;
use function array_filter;
use function array_map;
use function is_array;

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
        $ergonodeData = $productData->getErgonodeData();

        /* @todo add delete media part when not exist in Ergonode */

        $galleryAttribute = $ergonodeData->getMedia();
        if ($galleryAttribute === false) {
            return $productData;
        }

        $payloads = [];
        $index = 0;
        if ($galleryAttribute) {
            foreach ($galleryAttribute->getAllMultimedia() as $multimedia) {
                foreach ($multimedia->getTranslations() as $translation) {
                    $mediaId = $this->fileManager->persist($translation, $context);
                    if (isset($payloads[$mediaId])) {
                        continue;
                    }

                    $payloads[$mediaId] = $this->buildProductMediaPayload($mediaId, $index, $productData, $context);
                    if ($index === 0) {
                        $swData->setCover($payloads[$mediaId]);
                    }
                    $index++;
                }
            }
        }

        $swData->setMedia($payloads);

        $productData->setShopwareData($swData);

        $this->addEntitiesToDelete($productData);

        return $productData;
    }

    private function buildProductMediaPayload(
        string $mediaId,
        int $position,
        ProductTransformationDTO $productData,
        Context $context
    ): array {
        $productMedia = null;
        if (null !== $productData->getSwProduct()) {
            $productMedia = $this->productMediaProvider->getProductMedia(
                $mediaId,
                $productData->getSwProduct()->getId(),
                $context
            );
        }

        return [
            'id' => $productMedia ? $productMedia->getId() : Uuid::randomHex(),
            // need to generate uuid here so media won't be duplicated if used as cover
            'mediaId' => $mediaId,
            'position' => $position,
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

        $productMediaIds = $productMedia->getIds();

        $newMediaPayloads = $productData->getShopwareData()->getMedia();
        if (empty($newMediaPayloads)) {
            return array_map(fn(string $id) => ['id' => $id], $productMediaIds);
        }

        $newProductMediaIds = array_filter(
            array_map(fn(array $media) => $media['id'] ?? null, $newMediaPayloads)
        );

        $idsToDelete = array_diff($productMediaIds, $newProductMediaIds);

        return array_map(fn(string $id) => ['id' => $id], $idsToDelete);
    }

    private function addEntitiesToDelete(ProductTransformationDTO $productData): void
    {
        $toDelete = $this->getProductMediaDeletePayload($productData);
        if (!empty($toDelete)) {
            $productData->addEntitiesToDelete(
                ProductMediaDefinition::ENTITY_NAME,
                $toDelete
            );
        }
    }
}

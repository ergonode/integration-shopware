<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Model\ProductMultimediaTranslation;
use Ergonode\IntegrationShopware\Service\FileDownloader;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class FileManager
{
    private MediaService $mediaService;

    private FileDownloader $fileDownloader;

    private EntityRepository $mediaRepository;

    public function __construct(
        MediaService $mediaService,
        FileDownloader $fileDownloader,
        EntityRepository $mediaRepository
    ) {
        $this->mediaService = $mediaService;
        $this->fileDownloader = $fileDownloader;
        $this->mediaRepository = $mediaRepository;
    }

    public function persist(
        array $image,
        Context $context,
        string $folder = ProductDefinition::ENTITY_NAME
    ): ?string {
        if (empty($image->getUrl()) || empty($image->getExtension())) {
            return null;
        }

        $existingMedia = $this->getMediaEntity($image, $context, $folder);

        if (null !== $existingMedia) {
            return $existingMedia->getId();
        }

        $file = $this->fileDownloader->download($image->getUrl(), $image->getExtension());

        if (null === $file) {
            return null;
        }

        return $this->mediaService->saveMediaFile(
            $file,
            $this->buildFileName($image),
            $context,
            $folder,
            null,
            false
        );
    }

    private function getMediaEntity(
        array $image,
        Context $context,
        string $folder = ProductDefinition::ENTITY_NAME
    ): ?MediaEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $this->buildFileName($image)));
        $criteria->addFilter(new EqualsFilter('mediaFolder.defaultFolder.entity', $folder));
        $criteria->addAssociation('mediaFolder.defaultFolder');

        $media = $this->mediaRepository->search($criteria, $context)->first();

        if ($media instanceof MediaEntity) {
            return $media;
        }

        return null;
    }

    private function buildFileName(ProductMultimediaTranslation $image): string
    {
        $imageData = [
            'name' => $image->getName(),
            'extension' => $image->getExtension(),
            'mime' => $image->getMime(),
            'size' => $image->getSize(),
            'url' => $image->getUrl(),
        ];

        return md5(json_encode($imageData));
    }
}

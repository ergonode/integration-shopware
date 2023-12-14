<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Service\FileDownloader;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class FileManagerArray
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

    public function persist(array $image, Context $context, string $folder = ProductDefinition::ENTITY_NAME): ?string
    {
        if (empty($image['url']) || empty($image['extension'])) {
            return null;
        }

        $existingMedia = $this->getMediaEntity($image, $context, $folder);

        if (null !== $existingMedia) {
            return $existingMedia->getId();
        }

        $file = $this->fileDownloader->download($image['url'], $image['extension']);

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
        string $folder
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

    private function buildFileName(array $image): string
    {
        return md5(json_encode($image));
    }
}

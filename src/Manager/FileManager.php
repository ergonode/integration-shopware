<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Manager;

use Ergonode\IntegrationShopware\Service\FileDownloader;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class FileManager
{
    private MediaService $mediaService;

    private FileDownloader $fileDownloader;

    private EntityRepositoryInterface $mediaRepository;

    public function __construct(
        MediaService $mediaService,
        FileDownloader $fileDownloader,
        EntityRepositoryInterface $mediaRepository
    ) {
        $this->mediaService = $mediaService;
        $this->fileDownloader = $fileDownloader;
        $this->mediaRepository = $mediaRepository;
    }

    public function persist(array $image, Context $context): ?string
    {
        if (empty($image['url']) || empty($image['extension'])) {
            return null;
        }

        $existingMedia = $this->getMediaEntity($image, $context);

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
            ProductDefinition::ENTITY_NAME,
            null,
            false
        );
    }

    private function getMediaEntity(array $image, Context $context): ?MediaEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $this->buildFileName($image)));
        $criteria->addFilter(new EqualsFilter('mediaFolder.defaultFolder.entity', ProductDefinition::ENTITY_NAME));
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
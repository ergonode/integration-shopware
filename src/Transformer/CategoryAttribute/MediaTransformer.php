<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CategoryAttribute;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Ergonode\IntegrationShopware\Manager\FileManagerArray;
use Ergonode\IntegrationShopware\Transformer\CategoryDataTransformerInterface;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;

class MediaTransformer implements CategoryDataTransformerInterface
{
    private const SW_FIELD_MEDIA = 'media';
    private const SW_FIELD_MEDIA_ID = 'mediaId';

    public function __construct(
        private FileManagerArray $fileManager,
    ) {
    }

    public function transform(CategoryTransformationDTO $categoryData, Context $context): CategoryTransformationDTO
    {
        $swData = $categoryData->getShopwareData();

        if (!isset($swData[self::SW_FIELD_MEDIA])) {
            return $categoryData;
        }

        $image = $swData[self::SW_FIELD_MEDIA];

        $mediaId = $this->fileManager->persist($image, $context, CategoryDefinition::ENTITY_NAME);
        $swData[self::SW_FIELD_MEDIA_ID] = $mediaId;

        $categoryData->setShopwareData($swData);
        $categoryData->unsetSwData(self::SW_FIELD_MEDIA);

        return $categoryData;
    }
}

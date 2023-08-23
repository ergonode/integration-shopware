<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Manager\FileManager;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductGalleryMultimedia;
use Shopware\Core\Framework\Context;

use function in_array;

class MediaProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private FileManager $fileManager;

    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    public function supports(ProductAttribute $attribute): bool
    {
        return in_array($attribute->getType(), [
            ProductAttribute::TYPE_IMAGE,
            ProductAttribute::TYPE_FILE,
            ProductAttribute::TYPE_GALLERY,
        ]);
    }

    public function transformNode(ProductAttribute $attribute, string $customFieldName, Context $context): array
    {
        $multimedia = null;
        switch ($attribute->getType()) {
            case ProductAttribute::TYPE_GALLERY:
            case ProductAttribute::TYPE_FILE:
                $multimedias = $attribute->getAllMultimedia();
                $key = array_key_first($multimedias);
                $multimedia = $multimedias[$key] ?? null;
                break;
            case ProductAttribute::TYPE_IMAGE:
                $multimedia = $attribute->getMultimedia();
                break;
        }

        if (!$multimedia instanceof ProductGalleryMultimedia) {
            return [];
        }

        $customFields = [];
        foreach ($multimedia->getTranslations() as $translation) {
            $mediaId = $this->fileManager->persist($translation, $context);

            $customFields[$translation->getLanguage()]['customFields'][$customFieldName] = $mediaId;
        }

        return $customFields;
    }
}

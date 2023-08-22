<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Manager\FileManager;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Shopware\Core\Framework\Context;

use function in_array;
use function is_array;
use function reset;

class MediaProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    private FileManager $fileManager;

    public function __construct(
        TranslationTransformer $translationTransformer,
        FileManager $fileManager
    ) {
        $this->translationTransformer = $translationTransformer;
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
        $translated = $this->translationTransformer->transform(
            $node['translations']
        );

        foreach ($translated as &$value) {
            $firstImage = reset($value);
            if (is_array($firstImage)) {
                $value = $firstImage; // in case $value is MultimediaArrayAttribute, use first element
            }

            $mediaId = $this->fileManager->persist($value, $context);

            $value = [
                'customFields' => [
                    $customFieldName => $mediaId,
                ],
            ];
        }

        return $translated;
    }
}

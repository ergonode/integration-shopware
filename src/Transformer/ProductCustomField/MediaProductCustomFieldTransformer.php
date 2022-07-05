<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\ProductCustomField;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Enum\AttributeTypesEnum;
use Strix\Ergonode\Manager\FileManager;
use Strix\Ergonode\Transformer\TranslationTransformer;
use Strix\Ergonode\Util\CustomFieldUtil;

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

    public function supports(array $node): bool
    {
        return in_array(AttributeTypesEnum::getNodeType($node['attribute']), [
            AttributeTypesEnum::IMAGE,
            AttributeTypesEnum::FILE,
            AttributeTypesEnum::GALLERY,
        ]);
    }

    public function transformNode(array $node, Context $context): array
    {
        $code = $node['attribute']['code'];

        $translated = $this->translationTransformer->transform(
            $node['valueTranslations']
        );

        foreach ($translated as &$value) {
            $firstImage = reset($value);
            if (is_array($firstImage)) {
                $value = $firstImage; // in case $value is MultimediaArrayAttribute, use first element
            }

            $mediaId = $this->fileManager->persist($value, $context);

            $value = [
                'customFields' => [
                    CustomFieldUtil::buildCustomFieldName($code) => $mediaId,
                ],
            ];
        }

        return $translated;
    }
}
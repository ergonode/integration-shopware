<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\Defaults;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Transformer\TranslationTransformer;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class SelectCustomFieldTransformer implements CustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    public function __construct(
        TranslationTransformer $translationTransformer
    ) {
        $this->translationTransformer = $translationTransformer;
    }

    public function supports(array $node): bool
    {
        return in_array(
            AttributeTypesEnum::getNodeType($node),
            [
                AttributeTypesEnum::SELECT,
                AttributeTypesEnum::MULTISELECT,
            ]);
    }

    public function transformNode(array $node): array
    {
        $options = [];

        foreach ($node['options'] as $option) {
            $label = $this->translationTransformer->transform($option['label']);

            $options[] = [
                'value' => $option['code'],
                'label' => empty($label) ? [Defaults::LANGUAGE_SYSTEM => $option['code']] : $label,
            ];
        }

        $isMultiSelect = AttributeTypesEnum::MULTISELECT === AttributeTypesEnum::getNodeType($node);

        return [
            'type' => CustomFieldTypes::SELECT,
            'config' => [
                'options' => $options,
                'componentName' => $isMultiSelect ? 'sw-multi-select' : 'sw-single-select',
                'customFieldType' => 'select',
            ],
        ];
    }
}
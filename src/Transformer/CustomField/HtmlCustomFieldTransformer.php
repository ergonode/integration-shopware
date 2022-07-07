<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class HtmlCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::TEXTAREA === AttributeTypesEnum::getNodeType($node) &&
            isset($node['additional_richEdit']) &&
            true === $node['additional_richEdit'];
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::HTML,
            'config' => [
                'componentName' => 'sw-text-editor',
                'customFieldType' => 'textEditor',
            ],
        ];
    }
}
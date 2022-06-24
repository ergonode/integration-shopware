<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class NumericCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::NUMERIC === AttributeTypesEnum::getAttributeNodeType($node);
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::FLOAT,
            'config' => [
                'type' => 'number',
                'numberType' => 'float',
                'componentName' => 'sw-field',
                'customFieldType' => 'number',
            ],
        ];
    }
}
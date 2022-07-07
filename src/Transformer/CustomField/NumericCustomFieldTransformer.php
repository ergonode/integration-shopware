<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Enum\AttributeTypesEnum;

use function in_array;

class NumericCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        $nodeType = AttributeTypesEnum::getNodeType($node);

        return in_array($nodeType, [
            AttributeTypesEnum::NUMERIC,
            AttributeTypesEnum::UNIT
        ]);
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
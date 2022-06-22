<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class PriceCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::PRICE === AttributeTypesEnum::getAttributeNodeType($node);
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::PRICE,
            'config' => [
                'customFieldType' => 'price',
            ],
        ];
    }
}
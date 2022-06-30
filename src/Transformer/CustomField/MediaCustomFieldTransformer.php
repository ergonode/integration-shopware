<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\CustomField;

use Shopware\Core\System\CustomField\CustomFieldTypes;
use Strix\Ergonode\Enum\AttributeTypesEnum;

class MediaCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::IMAGE === AttributeTypesEnum::getNodeType($node);
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::TEXT,
            'config' => [
                'componentName' => 'sw-media-field',
                'customFieldType' => 'media',
            ],
        ];
    }
}
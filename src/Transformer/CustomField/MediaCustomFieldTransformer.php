<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\System\CustomField\CustomFieldTypes;

use function in_array;

class MediaCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return in_array(AttributeTypesEnum::getNodeType($node), [
            AttributeTypesEnum::IMAGE,
            AttributeTypesEnum::FILE,
            AttributeTypesEnum::GALLERY,
        ]);
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
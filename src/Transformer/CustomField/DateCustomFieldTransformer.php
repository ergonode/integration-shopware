<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class DateCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::DATE === AttributeTypesEnum::getNodeType($node);
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::DATETIME,
            'config' => [
                'type' => 'date',
                'config' => [
                    'time_24hr' => true,
                ],
                'dateType' => 'datetime',
                'componentName' => 'sw-field',
                'customFieldType' => 'date',
            ],
        ];
    }
}
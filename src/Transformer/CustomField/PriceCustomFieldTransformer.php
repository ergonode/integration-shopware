<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class PriceCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::PRICE === AttributeTypesEnum::getNodeType($node);
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
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class RelationCustomFieldTransformer implements CustomFieldTransformerInterface
{
    public function supports(array $node): bool
    {
        return AttributeTypesEnum::RELATION === AttributeTypesEnum::getNodeType($node);
    }

    public function transformNode(array $node): array
    {
        return [
            'type' => CustomFieldTypes::SELECT,
            'config' => [
                'entity' => ProductDefinition::ENTITY_NAME,
                'componentName' => 'sw-entity-multi-id-select',
                'customFieldType' => CustomFieldTypes::ENTITY,
            ],
        ];
    }
}
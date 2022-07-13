<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\System\CustomField\CustomFieldTypes;

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
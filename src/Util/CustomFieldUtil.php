<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldUtil
{
    public static function buildCustomFieldName(string $code): string
    {
        return sprintf('%s_%s', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME, $code);
    }

    /**
     * @return string[]
     */
    public static function getValidErgonodeTypes(CustomFieldEntity $customField): array
    {
        $config = $customField->getConfig() ?: [];

        $customFieldType = $config['customFieldType'] ?? '';
        // special cases
        if (CustomFieldTypes::MEDIA === $customFieldType) {
            return [
                AttributeTypesEnum::IMAGE,
                AttributeTypesEnum::FILE,
                AttributeTypesEnum::GALLERY,
            ];
        }

        if (
            (CustomFieldTypes::ENTITY === $customFieldType) &&
            (ProductDefinition::ENTITY_NAME === ($config['entity'] ?? '')) &&
            CustomFieldTypes::SELECT === $customField->getType()
        ) {
            return [
                AttributeTypesEnum::RELATION,
            ];
        }

        if (
            (CustomFieldTypes::ENTITY === $customFieldType) &&
            CustomFieldTypes::SELECT === $customField->getType()
        ) {
            // not mappable
            return [];
        }

        // default cases
        switch ($customField->getType()) {
            case CustomFieldTypes::DATETIME:
                return [
                    AttributeTypesEnum::DATE,
                ];
            case CustomFieldTypes::FLOAT:
            case CustomFieldTypes::INT:
                return [
                    AttributeTypesEnum::NUMERIC,
                    AttributeTypesEnum::UNIT,
                ];
            case CustomFieldTypes::PRICE:
                return [
                    AttributeTypesEnum::PRICE,
                ];
            case CustomFieldTypes::SELECT:
                $isMultiSelect = 'sw-multi-select' === ($config['componentName'] ?? '');

                return $isMultiSelect ? [AttributeTypesEnum::MULTISELECT] : [AttributeTypesEnum::SELECT];
            case CustomFieldTypes::HTML:
            case CustomFieldTypes::TEXT:
                return [
                    AttributeTypesEnum::TEXT,
                    AttributeTypesEnum::TEXTAREA,
                ];
            case CustomFieldTypes::COLORPICKER:
            case CustomFieldTypes::ENTITY:
            case CustomFieldTypes::JSON:
            case CustomFieldTypes::SWITCH:
            default:
                return [];
        }
    }
}

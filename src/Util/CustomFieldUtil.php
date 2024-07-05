<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
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
                ProductAttribute::TYPE_IMAGE,
                ProductAttribute::TYPE_FILE,
                ProductAttribute::TYPE_GALLERY,
            ];
        }

        if (
            (CustomFieldTypes::ENTITY === $customFieldType) &&
            (ProductDefinition::ENTITY_NAME === ($config['entity'] ?? '')) &&
            CustomFieldTypes::SELECT === $customField->getType()
        ) {
            return [
                AttributeTypesEnum::RELATION,
                ProductAttribute::TYPE_PRODUCT_RELATION,
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
                    ProductAttribute::TYPE_DATE,
                ];
            case CustomFieldTypes::FLOAT:
            case CustomFieldTypes::INT:
                return [
                    AttributeTypesEnum::NUMERIC,
                    AttributeTypesEnum::UNIT,
                    ProductAttribute::TYPE_NUMERIC,
                    ProductAttribute::TYPE_UNIT,
                ];
            case CustomFieldTypes::PRICE:
                return [
                    AttributeTypesEnum::PRICE,
                    ProductAttribute::TYPE_PRICE,
                ];
            case CustomFieldTypes::SELECT:
                $isMultiSelect = 'sw-multi-select' === ($config['componentName'] ?? '');

                if ($isMultiSelect) {
                    return [
                        AttributeTypesEnum::MULTISELECT,
                        ProductAttribute::TYPE_MULTI_SELECT,
                    ];
                } else {
                    return [
                        AttributeTypesEnum::SELECT,
                        ProductAttribute::TYPE_SELECT,
                    ];
                }
            case CustomFieldTypes::HTML:
            case CustomFieldTypes::TEXT:
                return [
                    AttributeTypesEnum::TEXT,
                    AttributeTypesEnum::TEXTAREA,
                    ProductAttribute::TYPE_TEXT,
                    ProductAttribute::TYPE_TEXTAREA,
                ];
            case CustomFieldTypes::SWITCH:
            case CustomFieldTypes::BOOL:
                return [
                    AttributeTypesEnum::BOOL,
                    ProductAttribute::TYPE_SELECT,
                ];
            case CustomFieldTypes::COLORPICKER:
            case CustomFieldTypes::ENTITY:
            case CustomFieldTypes::JSON:
            default:
                return [];
        }
    }
}

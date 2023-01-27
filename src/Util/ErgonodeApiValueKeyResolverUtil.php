<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class ErgonodeApiValueKeyResolverUtil
{
    public const TYPE_VALUE_ARRAY = 'value_array';
    public const TYPE_VALUE_MULTI_ARRAY = 'value_multi_array';

    public static function resolve(string $typename): string
    {
        switch ($typename) {
            case 'TextAttributeValueTranslation':
            case 'TextareaAttributeValueTranslation':
            case 'DateAttributeValueTranslation':
            case 'UnitAttributeValueTranslation':
                return 'value_string';
            case 'NumericAttributeValueTranslation':
            case 'PriceAttributeValueTranslation':
                return 'value_numeric';
            case 'SelectAttributeValueTranslation':
                return self::TYPE_VALUE_ARRAY;
            case 'MultiSelectAttributeValueTranslation':
                return self::TYPE_VALUE_MULTI_ARRAY;
            case 'ImageAttributeValueTranslation':
                return 'value_multimedia';
            case 'FileAttributeValueTranslation':
            case 'GalleryAttributeValueTranslation':
                return 'value_multimedia_array';
            case 'ProductRelationAttributeValueTranslation':
                return 'value_product_array';
            default:
                throw new \RuntimeException(\sprintf('Unknown value typename: %s', $typename));
        }
    }
}

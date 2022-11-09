<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Enum;

class AttributeTypesEnum
{
    public const DATE = 'type_date';
    public const FILE = 'type_file';
    public const GALLERY = 'type_gallery';
    public const IMAGE = 'type_image';
    public const SELECT = 'type_select';
    public const MULTISELECT = 'type_multiselect';
    public const NUMERIC = 'type_numeric';
    public const PRICE = 'type_price';
    public const RELATION = 'type_relation';
    public const TEXTAREA = 'type_textarea';
    public const TEXT = 'type_text';
    public const UNIT = 'type_unit';
    public const TYPES = [
        self::DATE, self::FILE, self::GALLERY, self::IMAGE, self::SELECT, self::MULTISELECT, self::NUMERIC, self::PRICE,
        self::RELATION, self::TEXTAREA, self::TEXT, self::UNIT,
    ];

    public static function getNodeType(array $attribute): string
    {
        if (empty($attribute['code'])) {
            return self::TEXT;
        }

        $type = array_intersect(self::TYPES, array_keys($attribute));

        return reset($type) ?: self::TEXT;
    }

    public static function getShortNodeType(array $attribute): string
    {
        return str_replace('type_', '', self::getNodeType($attribute));
    }
}

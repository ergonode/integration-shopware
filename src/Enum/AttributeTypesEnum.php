<?php

declare(strict_types=1);

namespace Strix\Ergonode\Enum;

use ReflectionClass;

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

    public static function getAttributeNodeType(array $node): string
    {
        if (empty($node['code'])) {
            return self::TEXT;
        }

        $type = array_intersect(self::cases(), array_keys($node ?? []));

        return reset($type);
    }

    public static function cases(): array
    {
        $refl = new ReflectionClass(self::class);

        return array_values($refl->getConstants());
    }
}
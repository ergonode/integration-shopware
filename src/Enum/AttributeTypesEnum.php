<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Enum;

use Ergonode\IntegrationShopware\Util\Constants;

class AttributeTypesEnum
{
    public const SCOPE_GLOBAL = 'GLOBAL';
    public const SCOPE_LOCAL = 'LOCAL';

    public const BOOL = 'bool';

    public const DATE = 'date';
    public const FILE = 'file';
    public const GALLERY = 'gallery';
    public const IMAGE = 'image';
    public const SELECT = 'select';
    public const MULTISELECT = 'multiselect';
    public const NUMERIC = 'numeric';
    public const PRICE = 'price';
    public const RELATION = 'relation';
    public const TEXTAREA = 'textarea';
    public const TEXT = 'text';
    public const UNIT = 'unit';

    public const ERGONODE_TYPES = [
        self::DATE,
        self::FILE,
        self::GALLERY,
        self::IMAGE,
        self::SELECT,
        self::MULTISELECT,
        self::NUMERIC,
        self::PRICE,
        self::RELATION,
        self::TEXTAREA,
        self::TEXT,
        self::UNIT,
    ];

    public static function getNodeType(array $node): string
    {
        if (empty($node['code'])) {
            return self::TEXT;
        }

        $type = array_intersect(self::ERGONODE_TYPES, array_keys($node));

        return reset($type) ?: self::TEXT;
    }

    public static function isShopwareFieldOfType(string $shopwareKey, string $type): bool
    {
        return in_array($type, Constants::SW_PRODUCT_MAPPABLE_FIELDS[$shopwareKey] ?? []);
    }
}

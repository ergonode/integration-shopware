<?php

declare(strict_types=1);

namespace Strix\Ergonode\Util;

class Constants
{
    public const PRODUCT_CUSTOM_FIELD_SET_NAME = 'strix_ergonode_custom_fields';

    public const SW_PRODUCT_FIELD_NAME = 'name';
    public const SW_PRODUCT_FIELD_STOCK = 'stock';
    public const SW_PRODUCT_FIELD_MEDIA = 'media';

    public const SW_PRODUCT_MAPPABLE_FIELDS = [
        self::SW_PRODUCT_FIELD_NAME,
        self::SW_PRODUCT_FIELD_STOCK,
        self::SW_PRODUCT_FIELD_MEDIA,
    ];
}
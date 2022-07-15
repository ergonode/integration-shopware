<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class CustomFieldUtil
{
    public static function buildCustomFieldName(string $code): string
    {
        return sprintf('%s_%s', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME, $code);
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class PropertyGroupOptionUtil
{
    public static function buildOptionCode(string $prefix, string $suffix): string
    {
        return sprintf('%s_%s', $prefix, $suffix);
    }
}
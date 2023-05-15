<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class CodeBuilderUtil
{
    public const EXTENDED_JOIN = '__';

    public static function build(string $prefix, string $suffix): string
    {
        return sprintf('%s_%s', $prefix, $suffix);
    }

    public static function buildExtended(string $prefix, string $suffix): string
    {
        return sprintf('%s%s%s', $prefix, self::EXTENDED_JOIN, $suffix);
    }
}

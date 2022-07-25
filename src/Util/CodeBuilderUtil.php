<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class CodeBuilderUtil
{
    public static function build(string $prefix, string $suffix): string
    {
        return sprintf('%s_%s', $prefix, $suffix);
    }
}
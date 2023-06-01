<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class YesNo
{
    private const TRUE_LIKE = [
        true,
        'true',
        1,
        '1',
        'YES',
        'yes',
        'Y',
        'y',
        'A',
        'a',
    ];

    private const FALSE_LIKE = [
        false,
        'false',
        0,
        '0',
        'NO',
        'no',
        'N',
        'n',
        'B',
        'b',
    ];

    public static function cast(string|bool|int $value): bool
    {
        if (in_array($value, self::TRUE_LIKE, true)) {
            return true;
        } elseif (in_array($value, self::FALSE_LIKE, true)) {
            return false;
        }

        return false;
    }
}

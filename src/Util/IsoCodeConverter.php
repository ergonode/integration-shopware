<?php

declare(strict_types=1);

namespace Strix\Ergonode\Util;

use InvalidArgumentException;

class IsoCodeConverter
{
    /**
     * @param array|string $iso
     * @return array|string
     */
    public static function ergonodeToShopwareIso($iso)
    {
        if (!is_array($iso) && !is_string($iso)) {
            throw new InvalidArgumentException(
                sprintf('Expected argument 1 to be array or string. Got: %s', get_debug_type($iso))
            );
        }

        return str_replace('_', '-', $iso);
    }
}
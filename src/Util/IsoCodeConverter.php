<?php

declare(strict_types=1);

namespace Strix\Ergonode\Util;

class IsoCodeConverter
{
    public static function ergonodeToShopwareIso(string $iso): string
    {
        return str_replace('_', '-', $iso);
    }
}
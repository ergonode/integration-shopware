<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class IsoCodeConverter
{
    public static function ergonodeToShopwareIso(array|string $iso): array|string
    {
        return str_replace('_', '-', $iso);
    }

    public static function shopwareToErgonodeIso(string $iso): array|string
    {
        return str_replace('-', '_', $iso);
    }
}

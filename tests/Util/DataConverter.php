<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Util;

use Generator;

class DataConverter
{
    public static function arrayAsGenerator(array $array): Generator
    {
        foreach ($array as $item) {
            yield $item;
        }
    }
}
<?php

declare(strict_types=1);

namespace Strix\Ergonode\Enum;

use ReflectionClass;

abstract class AbstractEnum
{
    public static function cases(): array
    {
        $refl = new ReflectionClass(static::class);

        return array_values($refl->getConstants());
    }
}
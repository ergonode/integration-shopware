<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class ArrayUnfoldUtil
{
    public static function unfoldArray(array $array): array
    {
        $unfoldedResult = [];
        foreach ($array as $key => $value) {
            $keyChunks = \array_reverse(\explode('.', $key));
            $unfoldItem = self::unfoldItem($keyChunks, $value);
            $unfoldedResult = \array_merge_recursive($unfoldedResult, $unfoldItem);
        }

        return $unfoldedResult;
    }

    private static function unfoldItem(array $keyChunks, $value): array
    {
        if (1 === \count($keyChunks)) {
            return [$keyChunks[0] => $value];
        }

        $key = \array_pop($keyChunks);

        return [$key => self::unfoldItem($keyChunks, $value)];
    }
}
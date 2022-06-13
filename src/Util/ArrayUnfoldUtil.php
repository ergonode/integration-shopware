<?php

namespace Strix\Ergonode\Util;

class ArrayUnfoldUtil
{
    public function unfoldArray(array $array): array
    {
        $unfoldedResult = [];
        foreach ($array as $key => $value) {
            $keyChunks = \array_reverse(\explode('.', $key));
            $unfoldItem = $this->unfoldItem($keyChunks, $value);
            $unfoldedResult = \array_merge_recursive($unfoldedResult, $unfoldItem);
        }

        return $unfoldedResult;
    }

    private function unfoldItem(array $keyChunks, $value): array
    {
        if (1 === \count($keyChunks)) {
            return [$keyChunks[0] => $value];
        }

        $key = \array_pop($keyChunks);

        return [$key => $this->unfoldItem($keyChunks, $value)];
    }
}
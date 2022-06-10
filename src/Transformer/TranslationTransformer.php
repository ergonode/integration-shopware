<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Util\IsoCodeConverter;

class TranslationTransformer
{
    public function transform(array $ergonodeTranslation, string $shopwareKey): array
    {
        $translations = [];

        foreach ($ergonodeTranslation as $labelTranslation) {
            if (!empty($labelTranslation['language']) && !empty($labelTranslation['value'])) {
                $convertedIso = IsoCodeConverter::ergonodeToShopwareIso($labelTranslation['language']);
                $translations[$convertedIso][$shopwareKey] = $labelTranslation['value'];
            }
        }

        return $translations;
    }
}
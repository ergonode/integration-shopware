<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Util\IsoCodeConverter;

class TranslationTransformer
{
    public function transform(array $ergonodeTranslation, ?string $shopwareKey = null): array
    {
        $translations = [];

        foreach ($ergonodeTranslation as $labelTranslation) {
            if (!empty($labelTranslation['language']) && !empty($labelTranslation['value'])) {
                $convertedIso = IsoCodeConverter::ergonodeToShopwareIso($labelTranslation['language']);

                if (null !== $shopwareKey) {
                    $translations[$convertedIso][$shopwareKey] = $labelTranslation['value'];
                    
                    continue;
                }

                $translations[$convertedIso] = $labelTranslation['value'];
            }
        }

        return $translations;
    }
}
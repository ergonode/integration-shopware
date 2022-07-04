<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Strix\Ergonode\Util\ArrayUnfoldUtil;
use Strix\Ergonode\Util\ErgonodeApiValueKeyResolverUtil;
use Strix\Ergonode\Util\IsoCodeConverter;

use function stristr;

class TranslationTransformer
{
    public function transform(array $ergonodeTranslation, ?string $shopwareKey = null): array
    {
        $translations = [];

        foreach ($ergonodeTranslation as $translation) {
            if (!empty($translation['language'])) {
                $convertedIso = IsoCodeConverter::ergonodeToShopwareIso($translation['language']);

                $value = null;

                if (!empty($translation['value'])) {
                    $value = $translation['value'];
                } elseif (!empty($translation['__typename'])) {
                    $key = ErgonodeApiValueKeyResolverUtil::resolve($translation['__typename']);

                    if (!empty($translation[$key])) {
                        $value = $translation[$key];
                    }
                }

                if (null === $value) {
                    continue;
                }

                if (null !== $shopwareKey) {
                    $translations[$convertedIso][$shopwareKey] = $value;
                    if (stristr($shopwareKey, '.')) {
                        $translations[$convertedIso] = ArrayUnfoldUtil::unfoldArray($translations[$convertedIso]);
                    }

                    continue;
                }

                $translations[$convertedIso] = $value;
            }
        }

        return $translations;
    }
}
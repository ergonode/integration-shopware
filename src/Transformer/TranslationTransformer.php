<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Util\ArrayUnfoldUtil;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Ergonode\IntegrationShopware\Util\YesNo;
use Shopware\Core\Framework\Context;

use function stristr;

class TranslationTransformer
{
    private string $defaultLocale;

    private LanguageProvider $languageProvider;

    public function __construct(
        LanguageProvider $languageProvider
    ) {
        $this->languageProvider = $languageProvider;
    }

    public function transform(
        array $ergonodeTranslation,
        ?string $shopwareKey = null,
        ?bool $castValueToBool = false
    ): array {
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

                if ($castValueToBool) {
                    $value = YesNo::cast($value);
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

    public function transformDefaultLocale(array $ergonodeTranslation, Context $context): array
    {
        if (!isset($this->defaultLocale)) {
            $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
                $this->languageProvider->getDefaultLanguageLocale($context)
            );
        }

        foreach ($ergonodeTranslation as $translation) {
            if ($this->defaultLocale === $translation['language']) {
                $key = ErgonodeApiValueKeyResolverUtil::resolve($translation['__typename']);

                return $translation[$key];
            }
        }

        // default translation not found; return first one
        $value = reset($ergonodeTranslation);

        return $value[ErgonodeApiValueKeyResolverUtil::resolve($value['__typename'])];
    }
}

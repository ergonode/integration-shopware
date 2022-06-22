<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Locale\LocaleCollection;
use Shopware\Core\System\Locale\LocaleEntity;
use Strix\Ergonode\Api\LanguageListStreamResultsProxy;
use Strix\Ergonode\Provider\LocaleProvider;

class LanguagePersistor
{
    private EntityRepositoryInterface $languageRepository;

    private LocaleProvider $localeProvider;

    public function __construct(
        EntityRepositoryInterface $languageRepository,
        LocaleProvider $localeProvider
    ) {
        $this->languageRepository = $languageRepository;
        $this->localeProvider = $localeProvider;
    }

    public function persistStream(LanguageListStreamResultsProxy $languages, Context $context): array
    {
        $isoCodes = $languages->getIsoCodes();

        $newLocales = $this->filterNewLocales($isoCodes, $context);

        $languagesPayload = $newLocales->map(
            fn(LocaleEntity $locale) => [
                'localeId' => $locale->getId(),
                'translationCodeId' => $locale->getId(),
                'name' => sprintf('%s (%s)', $locale->getName(), $locale->getTerritory()),
            ]
        );

        if (empty($languagesPayload)) {
            return [];
        }

        $written = $this->languageRepository->create(array_values($languagesPayload), $context);

        return [
            LanguageDefinition::ENTITY_NAME => $written->getPrimaryKeys(LanguageDefinition::ENTITY_NAME),
        ];
    }

    private function filterNewLocales(array $isoCodes, Context $context): LocaleCollection
    {
        $swLocales = $this->localeProvider->getLocalesByIsoCodes($isoCodes, $context);

        return new LocaleCollection(
            $swLocales->filter(fn(LocaleEntity $locale) => null === $locale->getLanguages() || 0 === $locale->getLanguages()->count())
        );
    }
}
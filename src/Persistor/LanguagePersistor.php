<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Api\LanguageListStreamResultsProxy;
use Ergonode\IntegrationShopware\Provider\LocaleProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Locale\LocaleCollection;
use Shopware\Core\System\Locale\LocaleEntity;

class LanguagePersistor
{
    private EntityRepository $languageRepository;

    private LocaleProvider $localeProvider;

    public function __construct(
        EntityRepository $languageRepository,
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

        return $written->getPrimaryKeys(LanguageDefinition::ENTITY_NAME);
    }

    private function filterNewLocales(array $isoCodes, Context $context): LocaleCollection
    {
        $swLocales = $this->localeProvider->getLocalesByIsoCodes($isoCodes, $context);

        return new LocaleCollection(
            $swLocales->filter(fn(LocaleEntity $locale) => null === $locale->getLanguages() || 0 === $locale->getLanguages()->count())
        );
    }
}

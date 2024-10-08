<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use RuntimeException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;

class LanguageProvider
{
    private EntityRepository $languageRepository;

    public function __construct(EntityRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return string Default language locale in Shopware format (ex. en-GB)
     */
    public function getDefaultLanguageLocale(Context $context): string
    {
        $criteria = new Criteria([Defaults::LANGUAGE_SYSTEM]);
        $criteria->addAssociation('locale');

        $languageEntity = $this->languageRepository->search($criteria, $context)->first();
        if (!$languageEntity instanceof LanguageEntity) {
            throw new RuntimeException('Could not load default system language entity');
        }

        return $languageEntity->getLocale()->getCode();
    }

    public function getLocaleCodeByContext(Context $context): ?string
    {
        $criteria = new Criteria([$context->getLanguageId()]);
        $criteria->addAssociation('locale');

        $language = $this->languageRepository->search($criteria, $context)->first();

        if ($language instanceof LanguageEntity) {
            $locale = $language->getLocale();

            if (null !== $locale) {
                return $locale->getCode();
            }
        }

        return null;
    }

    public function getActiveLocaleCodes(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');

        /** @var LanguageEntity[] $languages */
        $languages = $this->languageRepository->search($criteria, $context);

        $result = [];
        foreach ($languages as $language) {
            $result[] = $language->getLocale()->getCode();
        }

        return $result;
    }
}

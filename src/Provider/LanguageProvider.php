<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;

class LanguageProvider
{
    private EntityRepositoryInterface $languageRepository;

    public function __construct(EntityRepositoryInterface $languageRepository)
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

        /** @var LanguageEntity $languageEntity */
        $languageEntity = $this->languageRepository->search($criteria, $context)->first();

        if (null === $languageEntity) {
            throw new \RuntimeException('Could not load default system language entity');
        }

        return $languageEntity->getLocale()->getCode();
    }

    public function getActiveLanguages(Context $context): LanguageCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');

        return $this->languageRepository->search($criteria, $context)->getEntities();
    }
}

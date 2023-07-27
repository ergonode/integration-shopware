<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Doctrine\DBAL\Connection;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionDefinition;
use Ergonode\IntegrationShopware\Persistor\Helper\ExistingCategoriesHelper;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class CategoryPersistor
{
    private EntityRepositoryInterface $categoryRepository;

    private ExistingCategoriesHelper $categoriesHelper;

    private LanguageProvider $languageProvider;

    private Connection $connection;

    private string $defaultLocale;

    private LoggerInterface $logger;

    public function __construct(
        EntityRepositoryInterface $categoryRepository,
        LanguageProvider $languageProvider,
        ExistingCategoriesHelper $categoriesHelper,
        Connection $connection,
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoriesHelper = $categoriesHelper;
        $this->languageProvider = $languageProvider;
        $this->connection = $connection;
        $this->logger = $ergonodeSyncLogger;
    }

    /**
     * @return array Persisted primary keys
     */
    public function persist(array $edges, Context $context): array
    {
        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $codes = \array_map(fn($edge) => $edge['node']['code'] ?? null, $edges);
        $codes = \array_unique(\array_filter($codes));
        $this->categoriesHelper->load($codes, $context);

        $payloads = [];
        foreach ($edges as $edge) {
            $code = $edge['node']['code'] ?? null;
            $translations = $edge['node']['name'] ?? null;

            if (null === $translations) {
                continue;
            }

            $categoryPayload = $this->createCategoryPayload($code, $translations);
            if (null === $categoryPayload) {
                continue;
            }

            $this->logger->info('Processed category ', [
                'code' => $code
            ]);
            $payloads[] = $categoryPayload;
        }

        if (empty($payloads)) {
            return [];
        }

        return $this->categoryRepository->update($payloads, $context)->getPrimaryKeys(CategoryDefinition::ENTITY_NAME);
    }

    private function createCategoryPayload(
        string $code,
        array $nodeTranslations
    ): ?array {
        $existingCategoryId = $this->categoriesHelper->get($code);

        if (null === $existingCategoryId) {
            return null;
        }

        $categoryName = null;
        $translations = [];
        foreach ($nodeTranslations as $translation) {
            $translationLocale = $translation['language'] ?? null;
            $translationValue = $translation['value'] ?? null;

            if (null === $translationValue || null === $translationLocale) {
                continue;
            }

            if ($this->defaultLocale === $translationLocale) {
                $categoryName = $translationValue;
            }

            $translations[IsoCodeConverter::ergonodeToShopwareIso($translationLocale)]['name'] = $translationValue;
        }

        $categoryName = $categoryName ?? $code;

        return [
            'id' => $existingCategoryId,
            'name' => $categoryName,
            'translations' => $translations,
        ];
    }

    /**
     * @return int Number of deleted categories
     */
    public function removeCategoriesUpdatedAtBeforeTimestamp(int $timestamp): int
    {
        $result = $this->connection->executeStatement(
            \sprintf(
                'DELETE cat FROM %1$s cat
                 JOIN %2$s ext ON cat.ergonode_category_mapping_extension_id = ext.id
                 WHERE GREATEST(cat.created_at, COALESCE(cat.updated_at, 0)) < :timestamp
                 AND cat.ergonode_category_mapping_extension_id IS NOT NULL;',
                CategoryDefinition::ENTITY_NAME,
                ErgonodeCategoryMappingExtensionDefinition::ENTITY_NAME,
            ),
            [
                'timestamp' => (new \DateTime('@' . $timestamp))->format('Y-m-d H:i:s'),
            ],
        );

        if (is_int($result)) {
            return $result;
        }

        return 0;
    }
}

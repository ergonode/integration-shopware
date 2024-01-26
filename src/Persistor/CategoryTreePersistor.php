<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Doctrine\DBAL\Connection;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionDefinition;
use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Persistor\Helper\ExistingCategoriesHelper;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;

class CategoryTreePersistor
{
    private EntityRepository $categoryRepository;

    private ExistingCategoriesHelper $categoriesHelper;

    private CategoryOrderHelper $categoryOrderHelper;

    private ?string $lastRootCategoryId = null;

    private LanguageProvider $languageProvider;

    private Connection $connection;

    public function __construct(
        EntityRepository $categoryRepository,
        ExistingCategoriesHelper $existingCategoriesHelper,
        CategoryOrderHelper $categoryOrderHelper,
        LanguageProvider $languageProvider,
        Connection $connection
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoriesHelper = $existingCategoriesHelper;
        $this->categoryOrderHelper = $categoryOrderHelper;
        $this->languageProvider = $languageProvider;
        $this->connection = $connection;
    }

    /**
     * @return array Persisted primary keys
     */
    public function persistLeaves(array $leaves, string $treeCode, Context $context): array
    {
        $defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );
        $codes = \array_map(fn($node) => $node['node']['category']['code'], $leaves);
        $parentCodes = \array_filter(\array_map(fn($node) => $node['node']['parentCategory']['code'] ?? null, $leaves));
        $parentCodes[] = $treeCode;

        $codes = \array_merge($codes, $parentCodes);
        $codes = \array_unique(\array_filter($codes));

        $this->categoriesHelper->load($codes, $context);

        $parentIds = \array_filter(
            \array_map(
                fn($code) => $this->categoriesHelper->get($code),
                $parentCodes
            )
        );

        $this->categoryOrderHelper->load(
            $parentIds,
            $context
        );

        $payloads = [];

        foreach ($leaves as $leaf) {
            $node = $leaf['node'];
            $parentCategory = $node['parentCategory']['code'] ?? null;

            $leafPayload = $this->createCategoryLeafPayload(
                $node,
                $treeCode,
                $defaultLocale,
                $parentCategory,
                $this->getLastRootCategoryId()
            );

            $payloads[] = $leafPayload;

            // keep categories order on top level within same tree
            if ($parentCategory === null && isset($leafPayload['id'])) {
                $this->setLastRootCategoryId($leafPayload['id']);
            }
        }

        $writeResult = $this->categoryRepository->upsert($payloads, $context);

        $this->categoryOrderHelper->save($context);

        return $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME);
    }

    private function createCategoryLeafPayload(
        array $node,
        string $treeCode,
        string $defaultLocale,
        ?string $parentCode = null,
        ?string $lastRootCategoryId = null
    ): array {
        $code = $node['category']['code'];
        $existingCategoryId = $this->categoriesHelper->get($code);

        $parentId = $parentCode ? $this->categoriesHelper->get($parentCode) : null;

        $id = $existingCategoryId;
        $createCategory = false;
        if (null === $id) {
            $id = Uuid::randomHex();
            $this->categoriesHelper->set($code, $id);
            $createCategory = true;
        }

        $afterCategoryId = $parentCode ? $this->categoryOrderHelper->getLastCategoryIdForParent($parentId) : $lastRootCategoryId;
        if ($afterCategoryId === $id) {
            $afterCategoryId = null;
        }
        $this->categoryOrderHelper->set($parentId, $id);

        $categoryName = null;
        $translations = [];
        foreach ($node['category']['name'] ?? [] as $translation) {
            $translationLocale = $translation['language'] ?? null;
            $translationValue = $translation['value'] ?? null;

            if (null === $translationValue || null === $translationLocale) {
                continue;
            }

            if ($defaultLocale === $translationLocale) {
                $categoryName = $translationValue;
            }

            $translations[IsoCodeConverter::ergonodeToShopwareIso($translationLocale)]['name'] = $translationValue;
        }

        $result = [
            'id' => $id,
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId,
            'translations' => $translations,
            'name' => $categoryName ?? $code,
        ];

        if ($createCategory) {
            $result[ErgonodeCategoryMappingExtension::EXTENSION_NAME] = [
                'code' => $code,
                'treeCode' => $treeCode,
                'locale' => null,
            ];
        }

        return $result;
    }

    public function setLastRootCategoryId(string $lastRootCategoryId): void
    {
        $this->lastRootCategoryId = $lastRootCategoryId;
    }

    public function resetLastRootCategoryId(): void
    {
        $this->lastRootCategoryId = null;
    }

    public function getLastRootCategoryId(): ?string
    {
        return $this->lastRootCategoryId;
    }

    public function removeCategoriesById(array $categoryIds, Context $context): void
    {
        $this->categoryRepository->delete($categoryIds, $context);
    }

    public function fetchCategoriesToDelete(): array
    {
        return $this->connection->fetchAllAssociative(
                'SELECT 
                            cme.code AS code,
                            LOWER(HEX(c.id)) AS id
                        FROM category c
                        INNER JOIN ergonode_category_mapping_extension cme 
                            ON cme.id = c.ergonode_category_mapping_extension_id
                        WHERE cme.active = 0    
        ');
    }

    public function markCategoriesAsActive(array $categoryIds): void
    {
        $categoryIds = array_map(function ($record) {
            return '0x'.$record;
        }, $categoryIds);

        if ($categoryIds === []) {
            return;
        }

        $this->connection->executeStatement(
                sprintf('UPDATE ergonode_category_mapping_extension SET active=1
                        WHERE id IN (SELECT ergonode_category_mapping_extension_id 
			                FROM category WHERE id IN (%s))', implode(', ', $categoryIds))
        );
    }

    public function clearCategoriesAsActive(): void
    {
        $this->connection->executeStatement(
                'UPDATE ergonode_category_mapping_extension SET active=0'
        );
    }
}

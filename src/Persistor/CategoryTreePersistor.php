<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Persistor\Helper\ExistingCategoriesHelper;
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

    public function __construct(
        EntityRepository $categoryRepository,
        ExistingCategoriesHelper $existingCategoriesHelper,
        CategoryOrderHelper $categoryOrderHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoriesHelper = $existingCategoriesHelper;
        $this->categoryOrderHelper = $categoryOrderHelper;
    }

    /**
     * @return array Persisted primary keys
     */
    public function persistLeaves(array $leaves, string $treeCode, Context $context): array
    {
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
                $node['category']['code'],
                $treeCode,
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
        string $code,
        string $treeCode,
        ?string $parentCode = null,
        ?string $lastRootCategoryId = null
    ): array {
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
        $this->categoryOrderHelper->set($parentId, $id);

        $result = [
            'id' => $id,
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId,
        ];

        if ($createCategory) {
            $result['name'] = $code;
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
}

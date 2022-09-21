<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Persistor\Helper\CategoryOrderHelper;
use Ergonode\IntegrationShopware\Persistor\Helper\ExistingCategoriesHelper;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;

class CategoryTreePersistor
{
    private EntityRepositoryInterface $categoryRepository;
    private ExistingCategoriesHelper $categoriesHelper;
    private CategoryOrderHelper $categoryOrderHelper;

    public function __construct(
        EntityRepositoryInterface $categoryRepository,
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
            $payloads[] = $this->createCategoryLeafPayload(
                $node['category']['code'],
                $treeCode,
                $node['parentCategory']['code'] ?? null
            );
        }

        $writeResult = $this->categoryRepository->upsert($payloads, $context);

        $this->categoryOrderHelper->save($context);

        return $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME);
    }

    private function createCategoryLeafPayload(
        string $code,
        string $treeCode,
        ?string $parentCode = null
    ): array {
        $existingCategoryId = $this->categoriesHelper->get($code);

        if (null === $parentCode) {
            if ($code === $treeCode) {
                // this is tree root category
                $parentId = null;
            } else {
                $parentId = $this->categoriesHelper->get($treeCode);
            }
        } else {
            $parentId = $this->categoriesHelper->get($parentCode);
        }

        $id = null === $existingCategoryId ? null : $existingCategoryId;
        $createCategory = false;
        if (null === $id) {
            $id = Uuid::randomHex();
            $this->categoriesHelper->set($code, $id);
            $createCategory = true;
        }

        $afterCategoryId = null;
        if (null !== $parentId) {
            $afterCategoryId = $this->categoryOrderHelper->getLastCategoryIdForParent($parentId);
            $this->categoryOrderHelper->set($parentId, $id);
        }

        $result = [
            'id' => $id,
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId
        ];

        if ($createCategory) {
            $result['name'] = $code;
            $result[ErgonodeCategoryMappingExtension::EXTENSION_NAME] = [
                'code' => $code,
                'locale' => null
            ];
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Persistor\Helper\ExistingCategoriesHelper;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;

class CategoryTreePersistor
{
    private EntityRepositoryInterface $categoryRepository;
    private ExistingCategoriesHelper $categoriesHelper;

    public function __construct(
        EntityRepositoryInterface $categoryRepository,
        ExistingCategoriesHelper $categoriesHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoriesHelper = $categoriesHelper;
    }

    /**
     * @return array Persisted primary keys
     */
    public function persistLeaves(array $leaves, string $treeCode, Context $context): array
    {
        $codes = \array_map(fn($node) => $node['node']['category']['code'], $leaves);
        $parentCodes = \array_map(fn($node) => $node['node']['parentCategory']['code'] ?? null, $leaves);

        $codes = \array_merge($codes, $parentCodes);
        $codes = \array_unique(\array_filter($codes));
        $codes[] = $treeCode;

        $this->categoriesHelper->load($codes, $context);

        $payloads = [];
        if (false === $this->categoriesHelper->has($treeCode)) {
            $payloads[] = $this->createCategoryLeafPayload($treeCode, $treeCode);
        }

        foreach ($leaves as $leaf) {
            $node = $leaf['node'];
            $payloads[] = $this->createCategoryLeafPayload(
                $node['category']['code'],
                $treeCode,
                $node['parentCategory']['code'] ?? null
            );
        }

        $writeResult = $this->categoryRepository->upsert($payloads, $context);

        return $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME);
    }

    private function createCategoryLeafPayload(
        string $code,
        string $treeCode,
        ?string $parentCode = null
    ): array {
        $existingCategoryId = $this->categoriesHelper->get($code);

        if (null === $parentCode) {
            $parentId = $this->categoriesHelper->get($treeCode);
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

        $result = [
            'id' => $id,
            'parentId' => $parentId,
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
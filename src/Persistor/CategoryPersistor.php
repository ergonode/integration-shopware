<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionEntity;
use Strix\Ergonode\Extension\ErgonodeCategoryMappingExtension;
use Strix\Ergonode\Struct\ErgonodeCategoryCollection;
use Strix\Ergonode\Provider\CategoryProvider;

class CategoryPersistor
{
    private array $parentMapping = [];

    private array $lastChildMapping = [];

    private EntityRepositoryInterface $categoryRepository;

    private CategoryProvider $categoryProvider;

    public function __construct(EntityRepositoryInterface $categoryRepository, CategoryProvider $categoryProvider)
    {
        $this->categoryRepository = $categoryRepository;
        $this->categoryProvider = $categoryProvider;
    }

    public function persist(array $categoryData, Context $context): void
    {
        foreach ($categoryData['name'] as $nameTranslation) {
            $payload = $this->createCategoryPayload(
                $categoryData['code'],
                $nameTranslation['value'],
                $nameTranslation['language'],
                $context
            );

            $categoryId = $payload['id'] ?? null;
            if (empty($categoryId)) {
                continue;
            }

            unset($payload['parentId'], $payload['afterCategoryId']);

            $this->categoryRepository->update(
                [
                    $payload
                ],
                $context
            );
        }
    }

    public function persistCollection(ErgonodeCategoryCollection $categoryCollection, Context $context): void
    {
        foreach ($categoryCollection as $category) {
            foreach ($category->getNameTranslations() as $categoryTranslation) {
                $parentId = null;
                if (null !== $category->getParentCategory()) {
                    $parentId = $this->getParentId(
                        $category->getParentCategory()->getCode(),
                        $categoryTranslation->getLocale()
                    );
                }

                $writeResult = $this->categoryRepository->upsert(
                    [
                        $this->createCategoryPayload(
                            $category->getCode(),
                            $categoryTranslation->getValue(),
                            $categoryTranslation->getLocale(),
                            $context,
                            $parentId,
                            $this->lastChildMapping[$parentId] ?? null
                        )
                    ],
                    $context
                );

                $categoryId = $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME)[0];

                $this->setParentMapping($category->getCode(), $categoryTranslation->getLocale(), $categoryId);
                $this->lastChildMapping[$parentId] = $categoryId;
            }
        }
    }

    private function setParentMapping(string $code, string $locale, string $parentId): void
    {
        $this->parentMapping[$locale][$code] = $parentId;
    }

    private function getParentId(string $code, string $locale): ?string
    {
        return $this->parentMapping[$locale][$code] ?? null;
    }

    private function createCategoryPayload(
        string $code,
        ?string $translated,
        string $locale,
        Context $context,
        ?string $parentId = null,
        ?string $afterCategoryId = null
    ): array {
        $existingCategory = $this->categoryProvider->getCategoryByMapping(
            $code,
            $locale,
            $context,
            [
                ErgonodeCategoryMappingExtension::EXTENSION_NAME
            ]
        );

        return [
            'id' => null === $existingCategory ? null : $existingCategory->getId(),
            'name' => empty($translated) ? $code . '_' . $locale : $translated,
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId,
            ErgonodeCategoryMappingExtension::EXTENSION_NAME => [
                'id' => null === $existingCategory ? null : $this->getEntityExtensionId($existingCategory),
                'code' => $code,
                'locale' => $locale
            ]
        ];
    }

    private function getEntityExtensionId(Entity $entity): ?string
    {
        $extension = $entity->getExtension(ErgonodeCategoryMappingExtension::EXTENSION_NAME);
        if ($extension instanceof ErgonodeCategoryMappingExtensionEntity) {
            return $extension->getId();
        }

        return null;
    }
}
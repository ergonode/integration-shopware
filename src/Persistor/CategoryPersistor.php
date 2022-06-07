<?php

declare(strict_types=1);

namespace Strix\Ergonode\Persistor;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Strix\Ergonode\Extension\ErgonodeCategoryMappingExtension;
use Strix\Ergonode\Modules\Category\Struct\ErgonodeCategoryCollection;
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

    public function persist(ErgonodeCategoryCollection $categoryCollection, Context $context): void
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

                $categoryId = $this->createCategory(
                    $category->getCode(),
                    $categoryTranslation->getValue(),
                    $categoryTranslation->getLocale(),
                    $context,
                    $parentId,
                    $this->lastChildMapping[$parentId] ?? null
                );

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

    private function createCategory(
        string $code,
        ?string $translated,
        string $locale,
        Context $context,
        ?string $parentId = null,
        ?string $afterCategoryId = null
    ): string {
        $existingCategory = $this->categoryProvider->getCategoryByMapping($code, $locale, $context);
        $writeResult = $this->categoryRepository->upsert(
            [[
                'id' => null === $existingCategory ? null : $existingCategory->getId(),
                'name' => empty($translated) ? $code . '_' . $locale : $translated,
                'parentId' => $parentId,
                'afterCategoryId' => $afterCategoryId,
                ErgonodeCategoryMappingExtension::EXTENSION_NAME => [
                    'code' => $code,
                    'locale' => $locale
                ]
            ]],
            $context
        );

        return $writeResult->getPrimaryKeys(CategoryDefinition::ENTITY_NAME)[0];
    }
}
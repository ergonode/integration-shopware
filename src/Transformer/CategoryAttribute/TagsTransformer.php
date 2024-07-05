<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CategoryAttribute;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping\ErgonodeCategoryAttributeMappingEntity;
use Ergonode\IntegrationShopware\Provider\CategoryAttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Transformer\CategoryDataTransformerInterface;
use Ergonode\IntegrationShopware\Util\ArrayUnfoldUtil;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Shopware\Core\Content\Category\Aggregate\CategoryTag\CategoryTagDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Tag\TagCollection;

class TagsTransformer implements CategoryDataTransformerInterface
{
    const ATTRIBUTE_CODE_TAGS = 'tags';

    const FILTER_ZERO = 'category';

    private string $defaultLocale;

    public function __construct(
        private CategoryAttributeMappingProvider $categoryAttributeMappingProvider,
        private LanguageProvider $languageProvider,
        private EntityRepository $tagRepository,
    ) {
    }

    public function transform(CategoryTransformationDTO $categoryData, Context $context): CategoryTransformationDTO
    {
        $mapping = $this->categoryAttributeMappingProvider->provideByShopwareKey(self::ATTRIBUTE_CODE_TAGS, $context);

        if (null === $mapping) {
            return $categoryData;
        }

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $tagMap = $this->getPayload($categoryData, $mapping, $context);

        if ($tagMap !== []) {
            $this->assignTagPayload($categoryData, $tagMap);
        }

        return $categoryData;
    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        $translatedValues = [];
        foreach ($valueTranslations as $valueTranslation) {
            $language = $valueTranslation['language'];
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);
            $values = [];
            foreach ($valueTranslation[$valueKey] as $record) {
                $values[] = empty($record['name'])
                    ? $record['code']
                    : $record['name'];
            }
            $translatedValues[$language] = $values;
        }
        return $translatedValues;
    }

    private function getPayload(
        CategoryTransformationDTO $categoryData,
        ?ErgonodeCategoryAttributeMappingEntity $mapping,
        Context $context
    ): array {
        $tagsFilter = [];
        // Creating criteria to find if tag from ergonode exist in Shopware
        foreach ($categoryData->getErgonodeCategoryData()['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            if ($code !== $mapping->getErgonodeKey()) {
                continue;
            }

            $translatedValues = $this->getTranslatedValues($edge['node']['translations']);

            foreach ($translatedValues[$this->defaultLocale] as $value) {
                $tagsFilter[$value] = new EqualsFilter('name', $value);
            }
        }

        // Filter to add existing tags for current category
        $tagsFilter[self::FILTER_ZERO] = new EqualsFilter('categories.id', $categoryData->getShopwareCategoryId());

        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter($tagsFilter));
        $tagCollection = $this->tagRepository->search($criteria, $context)->getEntities();

        return $this->prepareTagPayload($categoryData, $tagCollection, $tagsFilter);
    }

    private function prepareTagPayload(
        CategoryTransformationDTO $categoryData,
        TagCollection $tagCollection,
        array $tagsFilter,
    ): array {
        $tagMap = [];
        // Verification of existing tags, and assigning tags to delete
        foreach ($tagCollection as $tag) {
            if (!isset($tagsFilter[$tag->getName()])) {
                // Removing all tags that are not set in translations
                $categoryData->addEntitiesToDelete(
                    CategoryTagDefinition::ENTITY_NAME,
                    [
                        'categoryId' => $categoryData->getShopwareCategoryId(),
                        'tagId' => $tag->getId(),
                    ]
                );
                continue;
            }

            $tagMap[$tag->getName()] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ];
        }

        // Assigning new tags
        foreach ($tagsFilter as $tagName => $filter) {
            if (isset($tagMap[$tagName]) || $tagName === self::FILTER_ZERO) {
                continue;
            }

            $tagMap[$tagName] = [
                'id' => Uuid::randomHex(),
                'name' => $tagName,
            ];
        }
        return $tagMap;
    }

    private function assignTagPayload(CategoryTransformationDTO $categoryData, array $tagMap): void
    {
        $result = $categoryData->getShopwareData();

        $result['tags'] = array_values($tagMap);

        $categoryData->setShopwareData(
            ArrayUnfoldUtil::unfoldArray($result)
        );
    }
}

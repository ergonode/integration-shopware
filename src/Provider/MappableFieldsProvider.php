<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Util\Constants;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\CustomField\CustomFieldEntity;

use function array_keys;
use function array_map;

class MappableFieldsProvider implements MappableFieldsProviderInterface
{
    public function __construct(
        private readonly ErgonodeAttributeProvider $ergonodeAttributeProvider,
        private readonly EntityRepository $customFieldRepository,
        private readonly LanguageProvider $languageProvider,
        private readonly ErgonodeCategoryProvider $ergonodeCategoryProvider,
        private readonly ErgonodeTemplateProvider $ergonodeTemplateProvider,
        private readonly EntityRepository $categoryRepository,
        private readonly ConfigService $configService
    ) {
    }

    /**
     * @return string[]
     */
    public function getShopwareAttributes(): array
    {
        return array_keys(Constants::SW_PRODUCT_MAPPABLE_FIELDS);
    }

    public function getShopwareAttributesWithTypes(): array
    {
        $attributes = [];
        foreach (Constants::SW_PRODUCT_MAPPABLE_FIELDS as $code => $types) {
            $attributes[] = [
                'code' => $code,
                'type' => implode('/', $types),
                'translationKey' => Constants::SW_PRODUCT_TRANSLATION_KEYS[$code] ?? Constants::DEFAULT_TRANSLATION_KEY . $code,
            ];
        }

        return $attributes;
    }

    /**
     * @return string[]
     */
    public function getShopwareCustomFields(Context $context): array
    {
        return array_map(
            fn($data) => $data['code'],
            $this->getShopwareCustomFieldsWithTypes($context)
        );
    }

    public function getShopwareCustomFieldsWithTypes(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [
                    new EqualsFilter('customFieldSet.name', Constants::PRODUCT_CUSTOM_FIELD_SET_NAME),
                ]
            )
        );
        $criteria->addFilter(new EqualsFilter('customFieldSet.relations.entityName', ProductDefinition::ENTITY_NAME));

        $customFields = $this->customFieldRepository->search($criteria, $context)->getEntities();
        $localeCode = $this->languageProvider->getLocaleCodeByContext($context);

        return array_values(
            $customFields->map(function (CustomFieldEntity $customField) use ($localeCode) {
                $ergonodeTypes = CustomFieldUtil::getValidErgonodeTypes($customField);

                return [
                    'code' => $customField->getName(),
                    'type' => implode('/', $ergonodeTypes),
                    'label' => $customField->getConfig()
                        ? $customField->getConfig()['label'][$localeCode] ?? $customField->getName()
                        : $customField->getName(),
                ];
            })
        );
    }

    public function getErgonodeAttributes(array $types = []): array
    {
        return array_map(
            fn($data) => $data['code'],
            $this->getErgonodeAttributesWithTypes($types)
        );
    }

    public function getErgonodeAttributesWithTypes(array $types = []): array
    {
        $attributeCodes = [];

        $generator = $this->ergonodeAttributeProvider->provideProductAttributes();

        foreach ($generator as $attributes) {
            if (!empty($types)) {
                $attributes = $attributes->filterByAttributeTypes($types);
            }

            foreach ($attributes->getEdges() as $attribute) {
                $node = $attribute['node'] ?? null;
                $code = $node['code'] ?? null;
                if (null === $node || null === $code) {
                    continue;
                }

                $attributeCodes[] = [
                    'code' => $code,
                    'type' => AttributeTypesEnum::getNodeType($node),
                ];
            }
        }

        return $attributeCodes;
    }

    /**
     * @return string[]
     */
    public function getErgonodeCategoryTreeCodes(): array
    {
        $generator = $this->ergonodeCategoryProvider->provideCategoryTreeCodes();
        $treeCodes = [];

        foreach ($generator as $result) {
            $codes = array_map(fn(array $category) => $category['node']['code'] ?? null, $result->getEdges());
            $treeCodes[] = array_filter($codes);
        }

        return array_merge(...$treeCodes);
    }

    public function getShopwareCategories(Context $context): array
    {
        $criteria = new Criteria();

        /** @var CategoryCollection $categories */
        $categories = $this->categoryRepository->search($criteria, $context)->getEntities();

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'name' => $category->getTranslation('name'),
                'id' => $category->getId(),
            ];
        }

        $names = array_column($result, 'name');
        array_multisort($result, SORT_ASC, $names);

        return $result;
    }

    public function getErgonodeCategories(): array
    {
        $categoryTreeCodes = $this->configService->getCategoryTreeCodes();

        $categoryTrees = $this->ergonodeCategoryProvider->provideCategories($categoryTreeCodes);

        $result = [];
        foreach ($categoryTrees as $categoryTreeData) {
            foreach ($categoryTreeData as $category) {
                $code = $category['node']['category']['code'] ?? null;
                if (!$code) {
                    continue;
                }

                $result[] = [
                    'code' => $code,
                ];
            }
        }

        $codes = array_column($result, 'code');
        array_multisort($result, SORT_ASC, $codes);

        return $result;
    }

    public function getShopwareCategoriesAttributesWithTypes(): array
    {
        $attributes = [];
        foreach (Constants::SW_CATEGORY_ATTRIBUTES_MAPPABLE_FIELDS as $code => $types) {
            $attributes[] = [
                'code' => $code,
                'type' => implode('/', $types),
                'translationKey' => Constants::SW_CATEGORY_ATTRIBUTES_TRANSLATION_KEYS[$code]
                    ?? Constants::DEFAULT_TRANSLATION_KEY . $code,
            ];
        }

        return $attributes;
    }

    public function getErgonodeCategoryAttributesWithTypes(array $types = []): array
    {
        $attributeCodes = [];

        $generator = $this->ergonodeAttributeProvider->provideProductCategoryAttributes();

        foreach ($generator as $attributes) {
            if (!empty($types)) {
                $attributes = $attributes->filterByAttributeTypes($types);
            }

            foreach ($attributes->getEdges() as $attribute) {
                $node = $attribute['node'] ?? null;
                $code = $node['code'] ?? null;
                if (null === $node || null === $code) {
                    continue;
                }

                $attributeCodes[] = [
                    'code' => $code,
                    'type' => AttributeTypesEnum::getNodeType($node),
                ];
            }
        }

        return $attributeCodes;
    }

    public function getTemplates(): array
    {
        $templateCodes = [];

        $generator = $this->ergonodeTemplateProvider->provideTemplates();

        foreach ($generator as $templates) {
            foreach ($templates->getEdges() as $template) {
                $code = $template['node']['code'] ?? null;
                if (null === $code) {
                    continue;
                }

                $templateCodes[] = [
                    'code' => $code,
                ];
            }
        }

        return $templateCodes;
    }
}

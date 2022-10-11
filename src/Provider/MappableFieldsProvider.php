<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Struct\ErgonodeCategory;
use Ergonode\IntegrationShopware\Util\Constants;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\CustomField\CustomFieldEntity;

use function array_keys;
use function array_map;
use function strpos;
use function substr;

class MappableFieldsProvider
{
    private ErgonodeAttributeProvider $ergonodeAttributeProvider;

    private EntityRepositoryInterface $customFieldRepository;

    private LanguageProvider $languageProvider;

    private ErgonodeCategoryProvider $ergonodeCategoryProvider;

    public function __construct(
        ErgonodeAttributeProvider $ergonodeAttributeProvider,
        EntityRepositoryInterface $customFieldRepository,
        LanguageProvider $languageProvider,
        ErgonodeCategoryProvider $ergonodeCategoryProvider
    ) {
        $this->ergonodeAttributeProvider = $ergonodeAttributeProvider;
        $this->customFieldRepository = $customFieldRepository;
        $this->languageProvider = $languageProvider;
        $this->ergonodeCategoryProvider = $ergonodeCategoryProvider;
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
                $ergonodeTypes = array_map(fn(string $type) => str_replace('type_', '', $type), $ergonodeTypes);

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
                    'type' => $this->resolveErgonodeAttributeType($node),
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

    private function resolveErgonodeAttributeType(array $node): string
    {
        foreach ($node as $key => $value) {
            if (0 === strpos($key, 'type_')) {
                return substr($key, 5);
            }
        }

        return 'unknown';
    }
}

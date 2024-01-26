<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\CategoryAttribute;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping\ErgonodeCategoryAttributeMappingCollection;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum as Attr;
use Ergonode\IntegrationShopware\Provider\CategoryAttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Transformer\CategoryDataTransformerInterface;
use Ergonode\IntegrationShopware\Util\ArrayUnfoldUtil;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Ergonode\IntegrationShopware\Util\YesNo;
use RuntimeException;
use Shopware\Core\Framework\Context;
use function array_key_exists;
use function array_merge_recursive;
use function in_array;
use function is_array;
use function sprintf;

class GeneralTransformer implements CategoryDataTransformerInterface
{
    const KEY_FOR_REVERSE_VALUE = 'visible';
    private string $defaultLocale;

    private const TRANSLATABLE_KEYS = [
        'name',
        'description',
        'metaTitle',
        'metaDescription',
        'keywords',
    ];

    private const CATEGORY_KEYS = [
        'visible',
        'description',
        'linkNewTab',
        'linkType',
        'externalLink',
        'internalLink',
        'metaTitle',
        'metaDescription',
        'keywords',
    ];

    public function __construct(
        private CategoryAttributeMappingProvider $categoryAttributeMappingProvider,
        private LanguageProvider $languageProvider,
    ) {
    }

    public function transform(CategoryTransformationDTO $categoryData, Context $context): CategoryTransformationDTO
    {
        $ergonodeData = $categoryData->getErgonodeCategoryData();
        if (false === is_array($ergonodeData['attributeList']['edges'] ?? null)) {
            throw new RuntimeException('Invalid data format');
        }

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $result = $categoryData->getShopwareData();

        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappingKeys = $this->categoryAttributeMappingProvider->provideByErgonodeKey($code, $context);

            if (0 === $mappingKeys->count()) {
                continue;
            }

            $translatedValues = $this->getTranslatedValues($edge['node']['translations']);
            if (false === array_key_exists($this->defaultLocale, $translatedValues)) {
                throw new RuntimeException(
                    sprintf('Default locale %s not found in product data', $this->defaultLocale)
                );
            }

            $result = array_merge_recursive(
                $result,
                $this->getTransformedResult($mappingKeys, $translatedValues)
            );
        }

        $categoryData->setShopwareData(
            ArrayUnfoldUtil::unfoldArray($result)
        );

        $this->setupEmptyValues($categoryData, $context);

        return $categoryData;
    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        $translatedValues = [];
        foreach ($valueTranslations as $valueTranslation) {
            $language = $valueTranslation['language'];
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);
            switch ($valueKey) {
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_ARRAY:
                    if ($valueTranslation[$valueKey] === null) {
                        $translatedValues[$language] = null;
                        break;
                    }
                    $translatedValues[$language] = empty($valueTranslation[$valueKey]['name'])
                        ? $valueTranslation[$valueKey]['code']
                        : $valueTranslation[$valueKey]['name'];
                    break;
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_MULTI_ARRAY:
                    $values = [];
                    foreach ($valueTranslation[$valueKey] as $record) {
                        $values[] = empty($record['name'])
                            ? $record['code']
                            : $record['name'];
                    }
                    $translatedValues[$language] = $values;
                    break;
                default:
                    $translatedValues[$language] = $valueTranslation[$valueKey];
                    break;
            }
        }

        return $translatedValues;
    }

    private function getTransformedResult(
        ErgonodeCategoryAttributeMappingCollection $mappingKeys,
        array $translatedValues
    ): array {
        $result = [];
        foreach ($mappingKeys as $mappingEntity) {
            $swKey = $mappingEntity->getShopwareKey();
            $result[$swKey] = $translatedValues[$this->defaultLocale];
            $result = $this->getTranslations($translatedValues, $swKey, $result);

            if (Attr::isShopwareCategoryFieldOfType($swKey, Attr::BOOL)) {
                $result = $this->castResultsToBoolean($result);

                if ($swKey === self::KEY_FOR_REVERSE_VALUE) {
                    //Shopware shows reverse value for this field in admin panel.
                    $result[$swKey] = !$result[$swKey];
                }
            }
        }

        return $result;
    }

    private function getTranslations(array $translatedValues, string $swKey, array $result): array
    {
        foreach ($translatedValues as $locale => $value) {
            if (null === $value || false === in_array($swKey, self::TRANSLATABLE_KEYS)) {
                continue;
            }

            $swLocale = IsoCodeConverter::ergonodeToShopwareIso($locale);
            $result['translations'][$swLocale][$swKey] = $value;
        }

        return $result;
    }

    private function castResultsToBoolean(array $result): array
    {
        foreach ($result as &$value) {
            $value = YesNo::cast($value);
        }

        return $result;
    }

    private function setupEmptyValues(CategoryTransformationDTO $categoryData, Context $context): void
    {
        $shopwareData = $categoryData->getShopwareData();
        foreach (self::CATEGORY_KEYS as $swKey) {
            $mapping = $this->categoryAttributeMappingProvider->provideByShopwareKey($swKey, $context);

            if (null === $mapping) {
                //Mapping not set for field - removing values not allowed
                continue;
            }

            if (in_array($swKey, self::TRANSLATABLE_KEYS)) {
                //Field in translation
                //Setup all translations for null
                foreach ($categoryData->getSwLangCodes() as $langCode) {
                    if (!isset($shopwareData['translations'][$langCode][$swKey])) {
                        $shopwareData['translations'][$langCode][$swKey] = null;
                    }
                }

                continue;
            }

            //Field in main body
            if (!isset($shopwareData[$swKey])) {
                if (Attr::isShopwareCategoryFieldOfType($swKey, Attr::BOOL)) {
                    $shopwareData[$swKey] = false;

                    if ($swKey === self::KEY_FOR_REVERSE_VALUE) {
                        //Shopware shows reverse value for this field in admin panel.
                        $shopwareData[$swKey] = !$shopwareData[$swKey];
                    }
                    continue;
                }
                $shopwareData[$swKey] = null;
            }

        }
        $categoryData->setShopwareData($shopwareData);
    }
}

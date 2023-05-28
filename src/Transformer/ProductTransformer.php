<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum as Attr;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Provider\LocaleProvider;
use Ergonode\IntegrationShopware\Util\ArrayUnfoldUtil;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Ergonode\IntegrationShopware\Util\Constants;
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

class ProductTransformer implements ProductDataTransformerInterface
{
    const ALLOWED_CLEANUP_TYPES = [
        Attr::DATE,
        Attr::SELECT,
        Attr::MULTISELECT,
        Attr::NUMERIC,
        Attr::TEXTAREA,
        Attr::TEXT,
        Attr::UNIT,
    ];

    private string $defaultLocale;

    private const TRANSLATABLE_KEYS = [
        'name',
        'description',
        'metaDescription',
        'keywords',
        'metaTitle',
        'packUnit',
        'packUnitPlural',
        'customSearchKeywords',
        'scaleUnit',
    ];

    private AttributeMappingProvider $attributeMappingProvider;

    private LanguageProvider $languageProvider;

    private AttributeTypeValidator $attributeTypeValidator;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        LanguageProvider $languageProvider,
        AttributeTypeValidator $attributeTypeValidator
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->languageProvider = $languageProvider;
        $this->attributeTypeValidator = $attributeTypeValidator;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();
        if (false === is_array($ergonodeData['attributeList']['edges'] ?? null)) {
            throw new RuntimeException('Invalid data format');
        }

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $result = [];

        $attributeMap = $this->attributeMappingProvider->getAttributeMapByErgonodeKeys($context);

        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappingKeys = $this->attributeMappingProvider->provideByErgonodeKey($code, $context);

            $this->attributeTypeValidator->filterWrongAttributes(
                $edge['node']['attribute'] ?? [],
                $mappingKeys,
                $context,
                ['sku' => $ergonodeData['sku']]
            );

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

            // attribute is already processed
            if (isset($attributeMap[$code])) {
                unset($attributeMap[$code]);
            }
        }

        // unset values that did not come from Ergonode
        foreach ($attributeMap as $mappingEntity) {
            $types = Constants::SW_PRODUCT_MAPPABLE_FIELDS[$mappingEntity->getShopwareKey()] ?? [];
            if (empty($types)) {
                continue;
            }
            // allow cleanup only for particular types that have translations
            foreach ($types as $type) {
                if (!in_array($type, self::ALLOWED_CLEANUP_TYPES)) {
                    continue 2;
                }
            }

            // keep product name translations regardless if they are in Ergonode or not
            if ($mappingEntity->getShopwareKey() === 'name') {
                continue;
            }
            $result[$mappingEntity->getShopwareKey()] = null;

            $locales = $this->languageProvider->getActiveLocaleCodes($context);
            foreach ($locales as $locale) {
                $result['translations'][$locale][$mappingEntity->getShopwareKey()] = null;
            }
        }

        if ($productData->isUpdate()) {
            $result['id'] = $productData->getSwProduct()->getId();
        }

        $productData->setShopwareData(
            ArrayUnfoldUtil::unfoldArray($result)
        );

        return $productData;
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
        ErgonodeAttributeMappingCollection $mappingKeys,
        array $translatedValues
    ): array {
        $result = [];
        foreach ($mappingKeys as $mappingEntity) {
            $swKey = $mappingEntity->getShopwareKey();
            $result[$swKey] = $translatedValues[$this->defaultLocale];
            $result = $this->getTranslations($translatedValues, $swKey, $result);

            if (Attr::isShopwareFieldOfType($swKey, Attr::BOOL)) {
                $result = $this->castResultsToBoolean($result);
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
}

<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Exception\MissingRequiredProductMappingException;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Modules\Attribute\Provider\AttributeMappingProvider;
use Strix\Ergonode\Provider\LanguageProvider;
use Strix\Ergonode\Util\ArrayUnfoldUtil;
use Strix\Ergonode\Util\ErgonodeApiValueKeyResolverUtil;
use Strix\Ergonode\Util\IsoCodeConverter;

class ProductTransformer implements ProductDataTransformerInterface
{
    private string $defaultLocale;

    private const REQUIRED_KEYS = [
        'name',
        'stock',
        'tax.rate',
        'price.net',
        'price.gross'
    ];

    private const TRANSLATABLE_KEYS = [
        'name',
        'description',
        'metaDescription',
        'keywords',
        'metaTitle',
        'packUnit',
        'packUnitPlural',
        'customSearchKeywords',
    ];

    private AttributeMappingProvider $attributeMappingProvider;
    private LanguageProvider $languageProvider;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        LanguageProvider $languageProvider
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->languageProvider = $languageProvider;
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    public function transform(array $productData, Context $context): array
    {
        if (false === \is_array($productData['attributeList']['edges'] ?? null)) {
            throw new \RuntimeException('Invalid data format');
        }

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $result = [];

        foreach ($productData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappingKeys = $this->attributeMappingProvider->provideByErgonodeKey($code, $context);

            if (null === $mappingKeys) {
                continue;
            }

            $translatedValues = $this->getTranslatedValues($edge['node']['valueTranslations']);

            if (false === \array_key_exists($this->defaultLocale, $translatedValues)) {
                throw new \RuntimeException(
                    \sprintf('Default locale %s not found in product data', $this->defaultLocale)
                );
            }

            $result = \array_merge_recursive(
                $result,
                $this->getTransformedResult($mappingKeys, $translatedValues)
            );
        }


        $this->validateResult($result);

        return ArrayUnfoldUtil::unfoldArray($result);
    }

    /**
     * @throws MissingRequiredProductMappingException
     */
    private function validateResult(array $result): void
    {
        $missingAttributes = \array_diff(self::REQUIRED_KEYS, \array_keys($result));

        if (\count($missingAttributes) > 0) {
            throw new MissingRequiredProductMappingException($missingAttributes);
        }
    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        $translatedValues = [];
        foreach ($valueTranslations as $valueTranslation) {
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);
            $translatedValues[$valueTranslation['language']] = $valueTranslation[$valueKey];
        }

        return $translatedValues;
    }

    private function getTransformedResult(
        ErgonodeAttributeMappingCollection $mappingKeys,
        array $translatedValues
    ): array {
        $result = [];
        foreach ($mappingKeys as $ergonodeAttributeMappingEntity) {
            $swKey = $ergonodeAttributeMappingEntity->getShopwareKey();
            $result[$swKey] = $translatedValues[$this->defaultLocale];
            $result = $this->getTranslations($translatedValues, $swKey, $result);
        }

        return $result;
    }

    private function getTranslations(array $translatedValues, string $swKey, array $result): array
    {
        foreach ($translatedValues as $locale => $value) {
            if (null === $value || false === \in_array($swKey, self::TRANSLATABLE_KEYS)) {
                continue;
            }

            $swLocale = IsoCodeConverter::ergonodeToShopwareIso($locale);
            $result['translations'][$swLocale][$swKey] = $value;
        }

        return $result;
    }
}
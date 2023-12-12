<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductShopwareData;
use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductMultiSelectAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Transformer\Strategy\ProductResetValueStrategy;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Ergonode\IntegrationShopware\Util\Constants;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use Ergonode\IntegrationShopware\Util\YesNo;
use Shopware\Core\Framework\Context;

use function in_array;

class ProductTransformer implements ProductDataTransformerInterface
{
    private AttributeMappingProvider $attributeMappingProvider;

    private LanguageProvider $languageProvider;

    private AttributeTypeValidator $attributeTypeValidator;

    private ProductResetValueStrategy $resetValueStrategy;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        LanguageProvider $languageProvider,
        AttributeTypeValidator $attributeTypeValidator,
        ProductResetValueStrategy $resetValueStrategy
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->languageProvider = $languageProvider;
        $this->attributeTypeValidator = $attributeTypeValidator;
        $this->resetValueStrategy = $resetValueStrategy;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();
        $swData = $productData->getShopwareData();

        $defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
            $this->languageProvider->getDefaultLanguageLocale($context)
        );

        $mappings = $this->attributeMappingProvider->getAttributeMapByErgonodeKeys($context);
        foreach ($mappings as $mapping) {
            if (in_array($mapping->getShopwareKey(), Constants::MAPPINGS_WITH_SEPARATE_TRANSFORMERS)) {
                continue;
            }
            $code = $mapping->getErgonodeKey();
            $attribute = $ergonodeData->getAttributeByCode($code);
            if (!$attribute instanceof ProductAttribute) {
                $swData = $this->resetValueStrategy->resetValue($swData, $mapping);
                continue;
            }
            $mappingKeys = $this->attributeMappingProvider->provideByErgonodeKey($attribute->getCode(), $context);

            $this->attributeTypeValidator->isValid(
                $attribute,
                $mapping,
                $context,
                $ergonodeData->getSku()
            );

            if (0 === $mappingKeys->count()) {
                continue;
            }

            $swData = $this->resetValueStrategy->resetValue($swData, $mapping);

            $castToBool = AttributeTypesEnum::isShopwareProductFieldOfType($mapping->getShopwareKey(), 'bool');

            if ($attribute instanceof ProductMultiSelectAttribute) {
                $swData = $this->processMultiSelectAttribute($attribute, $swData, $defaultLocale, $mapping, $castToBool);
            } elseif ($attribute instanceof ProductSelectAttribute) {
                $swData = $this->processSelectAttribute($attribute, $swData, $defaultLocale, $mapping, $castToBool);
            } else {
                foreach ($attribute->getTranslations() as $translation) {
                    $value = $castToBool && !is_null($translation->getValue()) ? YesNo::cast($translation->getValue()) : $translation->getValue();
                    if ($translation->getLanguage() === $defaultLocale) {
                        $swData->setData($mapping->getShopwareKey(), $value);
                    }
                    $swData->setTranslatedField(
                        $mapping->getShopwareKey(),
                        IsoCodeConverter::ergonodeToShopwareIso($translation->getLanguage()),
                        $value
                    );
                }
                if (empty($attribute->getTranslations())) {
                    $swData = $this->resetValueStrategy->resetValue($swData, $mapping);
                }
            }
        }

        if ($productData->isUpdate()) {
            $swData->setId($productData->getSwProduct()->getId());
        }

        $productData->setShopwareData($swData);

        return $productData;
    }

    private function processMultiSelectAttribute(
        ProductMultiSelectAttribute $attribute,
        mixed $swData,
        string $defaultLocale,
        ErgonodeAttributeMappingEntity $mapping,
        bool $castToBool
    ): ProductShopwareData {
        $defaultData = [];
        foreach ($attribute->getOptions() as $option) {
            $defaultValue = $option->getCode();
            foreach ($option->getName() as $language => $value) {
                $translatedValue = $castToBool && !is_null($value) ? YesNo::cast($value) : $value;
                if ($language === $defaultLocale) {
                    if (is_null($value)) {
                        continue;
                    }
                    $defaultValue = $translatedValue;
                }

                $existingTranslation = $swData->getTranslatedField(
                    $mapping->getShopwareKey(),
                    IsoCodeConverter::ergonodeToShopwareIso($language)
                ) ?? [];
                $existingTranslation[] = $translatedValue;
                $swData->setTranslatedField(
                    $mapping->getShopwareKey(),
                    IsoCodeConverter::ergonodeToShopwareIso($language),
                    $existingTranslation
                );
            }
            $defaultData[] = $defaultValue;
        }

        $defaultTranslation = $swData->getTranslatedField(
            $mapping->getShopwareKey(),
            IsoCodeConverter::ergonodeToShopwareIso($defaultLocale)
        );
        $swData->setData($mapping->getShopwareKey(), $defaultData);
        if (empty($defaultTranslation)) {
            $swData->setTranslatedField(
                $mapping->getShopwareKey(),
                IsoCodeConverter::ergonodeToShopwareIso($defaultLocale),
                $defaultData
            );
        }
        if (empty($attribute->getOptions())) {
            $swData = $this->resetValueStrategy->resetValue($swData, $mapping);
        }

        return $swData;
    }

    private function processSelectAttribute(
        ProductSelectAttribute $attribute,
        ProductShopwareData $swData,
        string $defaultLocale,
        ErgonodeAttributeMappingEntity $mapping,
        bool $castToBool
    ): ProductShopwareData {
        foreach ($attribute->getOptions() as $option) {
            $swData->setTranslatedField(
                $mapping->getShopwareKey(),
                IsoCodeConverter::ergonodeToShopwareIso($defaultLocale),
                $option->getCode()
            );
            $swData->setData($mapping->getShopwareKey(), $option->getCode());
            foreach ($option->getName() as $language => $value) {
                $translatedValue = $castToBool && !is_null($value) ? YesNo::cast($value) : $value;
                if ($language === $defaultLocale) {
                    if (is_null($value)) {
                        continue;
                    }
                    $swData->setData($mapping->getShopwareKey(), $translatedValue);
                }
                $swData->setTranslatedField(
                    $mapping->getShopwareKey(),
                    IsoCodeConverter::ergonodeToShopwareIso($language),
                    $translatedValue
                );
            }
        }
        if (empty($attribute->getOptions())) {
            $swData = $this->resetValueStrategy->resetValue($swData, $mapping);
        }

        return $swData;
    }
}

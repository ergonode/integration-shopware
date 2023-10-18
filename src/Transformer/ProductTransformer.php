<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
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

            $castToBool = AttributeTypesEnum::isShopwareFieldOfType($mapping->getShopwareKey(), 'bool');

            if ($attribute instanceof ProductSelectAttribute) {
                foreach ($attribute->getOptions() as $option) {
                    $swData->setTranslatedField(
                        $mapping->getShopwareKey(),
                        IsoCodeConverter::ergonodeToShopwareIso($defaultLocale),
                        $option->getCode()
                    );
                    $swData->setData($mapping->getShopwareKey(), $option->getCode());
                    foreach ($option->getName() as $language => $value) {
                        $translatedValue = $castToBool ? YesNo::cast($value) : $value;
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
            } else {
                foreach ($attribute->getTranslations() as $translation) {
                    $value = $castToBool ? YesNo::cast($translation->getValue()) : $translation->getValue();
                    if ($translation->getLanguage() === $defaultLocale) {
                        $swData->setData($mapping->getShopwareKey(), $value);
                    }
                    $swData->setTranslatedField(
                        $mapping->getShopwareKey(),
                        IsoCodeConverter::ergonodeToShopwareIso($translation->getLanguage()),
                        $value
                    );
                }
            }
        }

        if ($productData->isUpdate()) {
            $swData->setId($productData->getSwProduct()->getId());
        }

        $productData->setShopwareData($swData);

        return $productData;
    }
}

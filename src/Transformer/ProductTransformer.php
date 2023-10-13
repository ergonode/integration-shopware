<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Util\AttributeTypeValidator;
use Ergonode\IntegrationShopware\Util\Constants;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
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

            if ($attribute instanceof ProductSelectAttribute) {
                foreach ($attribute->getOptions() as $option) {
                    $swData->setTranslatedField(
                        $mapping->getShopwareKey(),
                        IsoCodeConverter::ergonodeToShopwareIso($defaultLocale),
                        $option->getCode()
                    );
                    foreach ($option->getName() as $language => $value) {
                        if ($language === $defaultLocale && is_null($value)) {
                            continue;
                        }
                        $swData->setTranslatedField(
                            $mapping->getShopwareKey(),
                            IsoCodeConverter::ergonodeToShopwareIso($language),
                            $value
                        );
                    }
                }
            } else {
                foreach ($attribute->getTranslations() as $translation) {
                    $swData->setTranslatedField(
                        $mapping->getShopwareKey(),
                        IsoCodeConverter::ergonodeToShopwareIso($translation->getLanguage()),
                        $translation->getValue()
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

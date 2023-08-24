<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum as Attr;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
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
        $swData = $productData->getShopwareData();

        $this->defaultLocale = IsoCodeConverter::shopwareToErgonodeIso(
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

            $data = [];
            foreach ($attribute->getTranslations() as $translation) {
                $data['translations'][$translation->getLanguage()] = $translation->getValue();
            }

            $swData->setData($mapping->getShopwareKey(), $data);
        }

        if ($productData->isUpdate()) {
            $swData->setId($productData->getSwProduct()->getId());
        }

        $productData->setShopwareData($swData);

        return $productData;
    }
}

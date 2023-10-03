<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Factory;

use Ergonode\IntegrationShopware\DTO\ProductErgonodeData;
use Ergonode\IntegrationShopware\DTO\ProductShopwareData;
use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductAttributeOption;
use Ergonode\IntegrationShopware\Model\ProductFileAttribute;
use Ergonode\IntegrationShopware\Model\ProductGalleryAttribute;
use Ergonode\IntegrationShopware\Model\ProductGalleryMultimedia;
use Ergonode\IntegrationShopware\Model\ProductImageAttribute;
use Ergonode\IntegrationShopware\Model\ProductMultimediaTranslation;
use Ergonode\IntegrationShopware\Model\ProductMultiSelectAttribute;
use Ergonode\IntegrationShopware\Model\ProductPriceAttribute;
use Ergonode\IntegrationShopware\Model\ProductRelationAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Model\ProductSimpleAttributeTranslation;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Shopware\Core\Framework\Context;

class ProductDataFactory
{
    public function __construct(private readonly AttributeMappingProvider $mappingProvider)
    {
    }

    public function create(
        array $data,
        bool $isInitialPaginatedImport,
        Context $context,
        string $defaultLanguage
    ): ProductTransformationDTO {
        $mappings = $this->getMappings($context);
        $ergonodeData = $this->buildErgonodeData($data, $mappings);
        foreach ($data['variantList']['edges'] ?? [] as $variantEdge) {
            $variantData = $variantEdge['node'] ?? [];
            if (empty($variantData)) {
                continue;
            }
            $variant = $this->buildErgonodeData($variantData, $mappings);
            $ergonodeData->addVariant($variant);
        }

        return new ProductTransformationDTO(
            $ergonodeData,
            new ProductShopwareData([]),
            $defaultLanguage,
            $isInitialPaginatedImport
        );
    }

    private function transformProductAttribute(array $attributeData): ProductAttribute
    {
        $type = $attributeData['attribute']['__typename'];
        switch ($type) {
            case ProductAttribute::TYPE_MULTI_SELECT:
            case ProductAttribute::TYPE_SELECT:
                return $this->transformSelectAttribute($attributeData, $type);
            case ProductAttribute::TYPE_IMAGE:
                return $this->transformImageAttribute($attributeData, $type);
            case ProductAttribute::TYPE_FILE:
            case ProductAttribute::TYPE_GALLERY:
                return $this->transformGalleryAttribute($attributeData, $type);
            case ProductAttribute::TYPE_PRICE:
                return $this->transformPriceAttribute($attributeData, $type);
            case ProductAttribute::TYPE_PRODUCT_RELATION:
                return $this->transformRelationAttribute($attributeData, $type);
            default:
                $attribute = new ProductAttribute(
                    $attributeData['attribute']['code'],
                    $type
                );

                foreach ($attributeData['translations'] ?? [] as $translation) {
                    $attributeTranslation = new ProductSimpleAttributeTranslation(
                        $translation['value_string'] ?? ($translation['value_numeric'] ?? null),
                        $translation['language']
                    );

                    $attribute->addTranslation($attributeTranslation);
                }

                return $attribute;
        }
    }

    private function transformSelectAttribute(array $attributeData, string $type): ProductSelectAttribute
    {
        $attribute = $type === ProductAttribute::TYPE_SELECT
            ? new ProductSelectAttribute($attributeData['attribute']['code'], $type)
            : new ProductMultiSelectAttribute($attributeData['attribute']['code'], $type);

        $existingOptions = [];
        foreach ($attributeData['translations'] ?? [] as $translation) {
            $translationRecords = [];
            if (isset($translation['value_array'])) {
                $translationRecords = [$translation['value_array']];
            } elseif (isset($translation['value_multi_array'])) {
                $translationRecords = $translation['value_multi_array'];
            }

            $language = $translation['language'];
            foreach ($translationRecords as $translationData) {
                if (empty($translationData)) {
                    continue;
                }
                $code = $translationData['code'];
                // Ergonode graphql returns one option multiple times for each translation, process it just once
                if (isset($existingOptions[$code])) {
                    continue;
                }

                $attribute->addOption(
                    new ProductAttributeOption(strtolower($code), [$language => $translationData['name']])
                );
                $existingOptions[$code] = $code;
            }
        }

        return $attribute;
    }

    private function transformGalleryAttribute(array $attributeData, string $type): ProductGalleryAttribute
    {
        if ($type == ProductAttribute::TYPE_FILE) {
            $attribute = new ProductFileAttribute(
                $attributeData['attribute']['code'],
                $type,
            );
        } else {
            $attribute = new ProductGalleryAttribute(
                $attributeData['attribute']['code'],
                $type,
            );
        }

        $translations = $attributeData['translations'] ?? [];
        foreach ($translations as $translation) {
            $translationRecords = $translation['value_multimedia_array'];
            $language = $translation['language'];
            foreach ($translationRecords ?? [] as $translationRecord) {
                $multimedia = $attribute->getMultimedia($translationRecord['name']) ?? new ProductGalleryMultimedia(
                    $translationRecord['name']
                );
                $multimedia->addTranslation(
                    new ProductMultimediaTranslation(
                        $translationRecord['name'],
                        $translationRecord['extension'],
                        $translationRecord['mime'],
                        $translationRecord['size'],
                        $translationRecord['url'],
                        $language
                    )
                );

                $attribute->addMultimedia($multimedia);
            }
        }

        return $attribute;
    }

    private function transformImageAttribute(array $attributeData, string $type): ProductImageAttribute
    {
        $attribute = new ProductImageAttribute(
            $attributeData['attribute']['code'],
            $type,
        );

        $translations = $attributeData['translations'] ?? [];
        $multimedia = new ProductGalleryMultimedia(
            $translations[array_key_first($translations)]['value_multimedia']['name']
        );
        foreach ($translations as $translation) {
            $translationRecord = $translation['value_multimedia'];
            if (is_null($translationRecord)) {
                continue;
            }
            $language = $translation['language'];
            $multimedia->addTranslation(
                new ProductMultimediaTranslation(
                    $translationRecord['name'],
                    $translationRecord['extension'],
                    $translationRecord['mime'],
                    $translationRecord['size'],
                    $translationRecord['url'],
                    $language
                )
            );
        }
        $attribute->addMultimedia($multimedia);

        return $attribute;
    }

    private function transformRelationAttribute(array $attributeData, string $type): ProductRelationAttribute
    {
        $attribute = new ProductRelationAttribute(
            $attributeData['attribute']['code'],
            $type,
        );

        $translations = $attributeData['translations'] ?? [];
        foreach ($translations as $translation) {
            $translationRecords = $translation['value_product_array'];
            $language = $translation['language'];
            $skus = [];
            foreach ($translationRecords ?? [] as $translationRecord) {
                if (!isset($translationRecord['sku'])) {
                    continue;
                }
                $skus[] = $translationRecord['sku'];
            }

            $attribute->addTranslation(new ProductSimpleAttributeTranslation($skus, $language));
        }

        return $attribute;
    }

    private function transformPriceAttribute(array $attributeData, string $type): ProductPriceAttribute
    {
        $attribute = new ProductPriceAttribute(
            $attributeData['attribute']['code'],
            $type,
            $attributeData['attribute']['currency']
        );

        $translations = $attributeData['translations'] ?? [];
        foreach ($translations as $translation) {
            $attributeTranslation = new ProductSimpleAttributeTranslation(
                $translation['value_numeric'] ?? null,
                $translation['language']
            );

            $attribute->addTranslation($attributeTranslation);
        }

        return $attribute;
    }

    private function getMappings(Context $context): array
    {
        $mappings = $this->mappingProvider->getAttributeMapByErgonodeKeys($context);
        $result = [];
        foreach ($mappings as $mapping) {
            $result[$mapping->getShopwareKey()] = $mapping->getErgonodeKey();
        }

        return $result;
    }

    private function buildErgonodeData(array $data, array $mappings): ProductErgonodeData
    {
        $ergonodeData = new ProductErgonodeData($data['sku'], $data['__typename'], $mappings);
        foreach ($data['attributeList']['edges'] ?? [] as $attributeEdge) {
            $attributeData = $attributeEdge['node'];

            $attribute = $this->transformProductAttribute($attributeData);

            $ergonodeData->addAttribute($attribute);
        }

        foreach ($data['categoryList']['edges'] ?? [] as $categoryEdge) {
            $ergonodeData->addCategory($categoryEdge);
        }

        if (isset($data['bindings'])) {
            foreach ($data['bindings'] ?? [] as $binding) {
                $ergonodeData->addBinding($binding['code']);
            }
        }

        return $ergonodeData;
    }
}

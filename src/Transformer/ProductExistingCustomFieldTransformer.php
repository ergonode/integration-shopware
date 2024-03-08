<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Provider\LanguageProvider;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Util\CustomFieldTypeValidator;
use Shopware\Core\Framework\Context;

use function array_merge_recursive;

class ProductExistingCustomFieldTransformer implements ProductDataTransformerInterface
{
    private ProductCustomFieldTransformerResolver $transformerResolver;

    private AttributeMappingProvider $customFieldMappingProvider;

    private CustomFieldTypeValidator $validator;

    private LanguageProvider $languageProvider;

    public function __construct(
        ProductCustomFieldTransformerResolver $transformerResolver,
        AttributeMappingProvider $customFieldMappingProvider,
        CustomFieldTypeValidator $validator,
        LanguageProvider $languageProvider
    ) {
        $this->transformerResolver = $transformerResolver;
        $this->customFieldMappingProvider = $customFieldMappingProvider;
        $this->validator = $validator;
        $this->languageProvider = $languageProvider;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();
        $swData = $productData->getShopwareData();

        $customFields = [];

        foreach ($ergonodeData['attributeList']['edges'] as $edge) {
            $code = $edge['node']['attribute']['code'];
            $mappings = $this->customFieldMappingProvider->provideByErgonodeKey($code, $context);

            $this->validator->filterWrongAttributes(
                $edge['node']['attribute'] ?? [],
                $mappings,
                $context,
                ['sku' => $ergonodeData['sku']]
            );

            if (0 === $mappings->count()) {
                continue;
            }

            $node = $edge['node'];

            $typedTransformer = $this->transformerResolver->resolve($node);
            echo $code.' - '.get_class($typedTransformer).PHP_EOL;
            if (null === $typedTransformer) {
                continue;
            }

            foreach ($mappings as $mapping) {
                $customFields[] = $typedTransformer->transformNode(
                    $node,
                    $mapping->getShopwareKey(),
                    $context
                );
            }
        }

        $customFields = array_merge_recursive(...$customFields);
        $customFields = $this->settingEmptyValues($context, $customFields);

        $swData['translations'] = array_merge_recursive(
            $swData['translations'] ?? [],
            $customFields
        );

        $productData->setShopwareData($swData);

        return $productData;
    }

    public function settingEmptyValues(Context $context, array $customFields): array
    {
        $customFieldMappings = $this->customFieldMappingProvider->getAttributeMapByErgonodeKeys($context);
        $locales = $this->languageProvider->getActiveLocaleCodes($context);

        foreach ($customFieldMappings as $customField) {
            foreach ($locales as $locale) {
                if (!isset($customFields[$locale]['customFields'][$customField->getShopwareKey()])) {
                    $customFields[$locale]['customFields'][$customField->getShopwareKey()] = null;
                }
            }
        }

        return $customFields;
    }
}

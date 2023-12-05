<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Util\CustomFieldTypeValidator;
use Shopware\Core\Framework\Context;

use function array_merge_recursive;

class ProductMappedCustomFieldTransformer implements ProductDataTransformerInterface
{
    private ProductCustomFieldTransformerResolver $transformerResolver;

    private AttributeMappingProvider $customFieldMappingProvider;

    private CustomFieldTypeValidator $validator;

    public function __construct(
        ProductCustomFieldTransformerResolver $transformerResolver,
        AttributeMappingProvider $customFieldMappingProvider,
        CustomFieldTypeValidator $validator
    ) {
        $this->transformerResolver = $transformerResolver;
        $this->customFieldMappingProvider = $customFieldMappingProvider;
        $this->validator = $validator;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $ergonodeData = $productData->getErgonodeData();
        $swData = $productData->getShopwareData();

        $mappings = $this->customFieldMappingProvider->getAttributeMapByErgonodeKeys($context);
        $customFields = [];
        foreach ($mappings as $mapping) {
            $code = $mapping->getErgonodeKey();
            $attribute = $ergonodeData->getAttributeByCode($code);
            if (is_null($attribute)) {
                continue;
            }

            if (!$this->validator->isValid($attribute, $mapping, $context, $ergonodeData->getSku())) {
                continue;
            }

            $typedTransformer = $this->transformerResolver->resolve($attribute);
            if (null === $typedTransformer) {
                continue;
            }

            $customFields[] = $typedTransformer->transformNode(
                $attribute,
                $mapping->getShopwareKey(),
                $context
            );
        }

        if (empty($customFields)) {
            return $productData;
        }
        $customFields = array_merge_recursive(...$customFields);

        $swData->setCustomFields($customFields);

        $productData->setShopwareData($swData);

        return $productData;
    }
}

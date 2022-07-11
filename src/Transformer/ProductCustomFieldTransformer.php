<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Provider\ConfigProvider;
use Strix\Ergonode\Resolver\ProductCustomFieldTransformerResolver;

use function array_filter;
use function array_merge_recursive;
use function in_array;

class ProductCustomFieldTransformer implements ProductDataTransformerInterface
{
    private ConfigProvider $configProvider;

    private ProductCustomFieldTransformerResolver $transformerResolver;

    public function __construct(
        ConfigProvider $configProvider,
        ProductCustomFieldTransformerResolver $transformerResolver
    ) {
        $this->configProvider = $configProvider;
        $this->transformerResolver = $transformerResolver;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();

        $codes = $this->configProvider->getErgonodeCustomFieldKeys();

        $attributes = $this->getAttributesByCodes($productData->getErgonodeData(), $codes);

        $customFields = [];
        foreach ($attributes as $ergoCustomField) {
            $node = $ergoCustomField['node'];

            $typedTransformer = $this->transformerResolver->resolve($node);
            if (null === $typedTransformer) {
                continue;
            }

            $transformedValue = $typedTransformer->transformNode($node, $context);

            $customFields = array_merge_recursive(
                $customFields,
                $transformedValue
            );
        }

        $swData['translations'] = array_merge_recursive(
            $swData['translations'] ?? [],
            $customFields
        );

        $productData->setShopwareData($swData);

        return $productData;
    }

    private function getAttributesByCodes(array $ergonodeData, array $codes): array
    {
        return array_filter(
            $ergonodeData['attributeList']['edges'] ?? [],
            fn(array $attribute) => in_array($attribute['node']['attribute']['code'] ?? '', $codes)
        );
    }
}
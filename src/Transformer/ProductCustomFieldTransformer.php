<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Framework\Context;

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
            $node = $ergoCustomField['node'] ?? null;
            $code = $node['attribute']['code'] ?? null;

            if (empty($node) || empty($code)) {
                continue;
            }

            $typedTransformer = $this->transformerResolver->resolve($node);
            if (null === $typedTransformer) {
                continue;
            }

            $customFields[] = $typedTransformer->transformNode(
                $node,
                CustomFieldUtil::buildCustomFieldName($code),
                $context
            );
        }

        $customFields = array_merge_recursive(...$customFields);

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
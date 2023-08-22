<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Framework\Context;

use function array_filter;
use function array_merge_recursive;
use function in_array;

class ProductCustomFieldTransformer implements ProductDataTransformerInterface
{
    private ConfigService $configService;

    private ProductCustomFieldTransformerResolver $transformerResolver;

    public function __construct(
        ConfigService $configService,
        ProductCustomFieldTransformerResolver $transformerResolver
    ) {
        $this->configService = $configService;
        $this->transformerResolver = $transformerResolver;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        $swData = $productData->getShopwareData();

        $codes = $this->configService->getErgonodeCustomFieldKeys();

        $attributes = $productData->getErgonodeData()->getAttributesByCodes($codes);

        $customFields = [];
        foreach ($attributes as $attribute) {
            $typedTransformer = $this->transformerResolver->resolve($attribute);
            if (null === $typedTransformer) {
                continue;
            }

            $customFields[] = $typedTransformer->transformNode(
                $attribute,
                CustomFieldUtil::buildCustomFieldName($attribute->getCode()),
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

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Service\ConfigService;
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

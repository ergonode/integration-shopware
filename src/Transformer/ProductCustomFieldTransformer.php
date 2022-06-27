<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;
use Strix\Ergonode\Provider\ConfigProvider;
use Strix\Ergonode\Resolver\ProductCustomFieldTransformerResolver;

use function array_merge_recursive;

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
        $ergoResult = $productData->getErgonodeObject();

        $keys = $this->configProvider->getErgonodeCustomFields();

        $ergoResult = $ergoResult->filterAttributesByCodes($keys);
        $ergoCustomFields = $ergoResult->getAttributeList();

        $customFields = [];
        foreach ($ergoCustomFields as $ergoCustomField) {
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
            $swData['translations'],
            $customFields
        );

        $productData->setShopwareData($swData);

        return $productData;
    }
}
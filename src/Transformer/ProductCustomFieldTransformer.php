<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Ergonode\IntegrationShopware\Resolver\ProductCustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Framework\Context;

use function array_merge_recursive;

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
        //@todo verify if resetting code is correct
        $swData->resetCustomFields();

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

        $swData->setCustomFields($customFields);

        $productData->setShopwareData($swData);

        return $productData;
    }
}

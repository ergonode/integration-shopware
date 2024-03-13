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

        $codes = $this->configService->getErgonodeCustomFieldKeys();

        $attributes = $productData->getErgonodeData()->getAttributesByCodes($codes);

        $customFields = [];
        $usedCustomFields = [];

        $unprocessedCodes = array_flip($codes);
        foreach ($attributes as $attribute) {
            $typedTransformer = $this->transformerResolver->resolve($attribute);
            if (null === $typedTransformer) {
                continue;
            }
            $usedCustomFields[] = $attribute->getCode();

            $customFields[] = $typedTransformer->transformNode(
                $attribute,
                CustomFieldUtil::buildCustomFieldName($attribute->getCode()),
                $context
            );

            unset($unprocessedCodes[$attribute->getCode()]);
        }

        $fieldsToRemove = array_diff($codes, $usedCustomFields);
        $customFields = array_merge_recursive(...$customFields);
        $customFields = $this->addEmptyValues($customFields, $fieldsToRemove);

        foreach ($unprocessedCodes as $unprocessedCode => $val) {
            foreach ($customFields as $language => $customFieldList) {
                $customFields[$language]['customFields'][CustomFieldUtil::buildCustomFieldName($unprocessedCode)] = null;
            }
        }

        $swData->setCustomFields($customFields);

        $productData->setShopwareData($swData);

        return $productData;
    }

    public function addEmptyValues(array $customFields, array $fieldsToRemove): array
    {
        foreach ($customFields as &$value) {
            foreach ($fieldsToRemove as $code) {
                $value['customFields'][CustomFieldUtil::buildCustomFieldName($code)] = null;
            }
        }

        return $customFields;
    }
}

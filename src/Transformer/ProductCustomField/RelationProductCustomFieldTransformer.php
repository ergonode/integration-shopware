<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Shopware\Core\Framework\Context;

use function is_array;

class RelationProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private const CUSTOM_FIELD_COMPONENT_NAME_SINGLE = 'sw-entity-single-select';

    private TranslationTransformer $translationTransformer;

    private ProductProvider $productProvider;

    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        TranslationTransformer $translationTransformer,
        ProductProvider $productProvider,
        CustomFieldProvider $customFieldProvider
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->productProvider = $productProvider;
        $this->customFieldProvider = $customFieldProvider;
    }

    public function supports(array $node): bool
    {
        return AttributeTypesEnum::RELATION === AttributeTypesEnum::getNodeType($node['attribute']);
    }

    public function transformNode(array $node, string $customFieldName, Context $context): array
    {
        $componentName = $this->getComponentNameByCustomFieldName($customFieldName, $context);
        $translated = $this->translationTransformer->transform(
            $node['translations']
        );

        foreach ($translated as &$value) {
            if (!is_array($value)) {
                $value = [];
                continue;
            }

            $ids = [];
            foreach ($value as $product) {
                if (isset($product['sku'])) {
                    $product = $this->productProvider->getProductBySku((string)$product['sku'], $context);
                    if (null === $product) {
                        continue; // product might not exist at this point; for example will be created later
                    }

                    $ids[] = $product->getId();
                }
            }

            if ($componentName != null && $componentName == self::CUSTOM_FIELD_COMPONENT_NAME_SINGLE) {
                $ids = $ids[0];
            }

            $value = [
                'customFields' => [
                    $customFieldName => $ids,
                ],
            ];
        }

        return $translated;
    }

    public function getComponentNameByCustomFieldName(string $customFieldName, Context $context): ?string
    {
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);
        return $customField->getConfig()['componentName'] ?? null;
    }
}

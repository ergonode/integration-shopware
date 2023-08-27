<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductRelationAttribute;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Shopware\Core\Framework\Context;

class RelationProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private const CUSTOM_FIELD_COMPONENT_NAME_SINGLE = 'sw-entity-single-select';

    private ProductProvider $productProvider;

    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        ProductProvider $productProvider,
        CustomFieldProvider $customFieldProvider
    ) {
        $this->productProvider = $productProvider;
        $this->customFieldProvider = $customFieldProvider;
    }

    public function supports(ProductAttribute $attribute): bool
    {
        return ProductAttribute::TYPE_PRODUCT_RELATION === $attribute->getType();
    }

    public function transformNode(ProductAttribute $attribute, string $customFieldName, Context $context): array
    {
        if (!$attribute instanceof ProductRelationAttribute) {
            return [];
        }
        $componentName = $this->getComponentNameByCustomFieldName($customFieldName, $context);

        $customFields = [];
        foreach ($attribute->getTranslations() as $translation) {
            $ids = [];
            foreach ($translation->getValue() as $sku) {
                $product = $this->productProvider->getProductBySku((string)$sku, $context);
                if (null === $product) {
                    continue; // product might not exist at this point; for example will be created later
                }
                if ($componentName != null && $componentName === self::CUSTOM_FIELD_COMPONENT_NAME_SINGLE) {
                    $ids = $product->getId();
                    break;
                }
                $ids[] = $product->getId();
            }

            $customFields[$translation->getLanguage(true)]['customFields'][$customFieldName] = $ids;
        }

        return $customFields;
    }

    public function getComponentNameByCustomFieldName(string $customFieldName, Context $context): ?string
    {
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);

        return $customField->getConfig()['componentName'] ?? null;
    }
}

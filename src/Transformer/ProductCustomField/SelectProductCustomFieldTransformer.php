<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Util\YesNo;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\CustomField\CustomFieldTypes;

use function in_array;

class SelectProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        CustomFieldProvider $customFieldProvider
    ) {
        $this->customFieldProvider = $customFieldProvider;
    }

    public function supports(ProductAttribute $attribute): bool
    {
        return in_array($attribute->getType(), [
            ProductAttribute::TYPE_SELECT,
            ProductAttribute::TYPE_MULTI_SELECT,
        ]);
    }

    public function transformNode(ProductAttribute $attribute, string $customFieldName, Context $context): array
    {
        if (!$attribute instanceof ProductSelectAttribute) {
            return [];
        }
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);

        $options = $attribute->getOptions();
        $selectedOption = $options[array_key_first($options)] ?? null;
        if (!$selectedOption) {
            return [];
        }
        $customFields = [];
        foreach ($selectedOption->getName() as $language => $name) {
            $value = $name;
            if (empty($value)) {
                $value = $selectedOption->getCode();
            }
            if (!is_null($customField) && $customField->getType() === CustomFieldTypes::BOOL) {
                $value = YesNo::cast($value);
            }
            $customFields[$language]['customFields'][$customFieldName] = $value;
        }

        return $customFields;
    }
}

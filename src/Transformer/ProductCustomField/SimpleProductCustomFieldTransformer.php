<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Util\YesNo;
use Shopware\Core\Framework\Context;

use Shopware\Core\System\CustomField\CustomFieldTypes;

use function in_array;

class SimpleProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
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
            ProductAttribute::TYPE_TEXT,
            ProductAttribute::TYPE_TEXTAREA,
            ProductAttribute::TYPE_NUMERIC,
            ProductAttribute::TYPE_UNIT,
            ProductAttribute::TYPE_DATE,
        ]);
    }

    public function transformNode(ProductAttribute $attribute, string $customFieldName, Context $context): array
    {
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);

        $customFields = [];
        foreach ($attribute->getTranslations() as $translation) {
            $value = $translation->getValue();
            if (!is_null($customField) && $customField->getType() === CustomFieldTypes::BOOL) {
                $value = YesNo::cast($value);
            }
            $customFields[$translation->getLanguage(true)]['customFields'][$customFieldName] = $value;
        }

        return $customFields;
    }
}

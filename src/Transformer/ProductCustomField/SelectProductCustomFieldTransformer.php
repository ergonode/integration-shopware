<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Model\ProductMultiSelectAttribute;
use Ergonode\IntegrationShopware\Model\ProductSelectAttribute;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
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
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);

        if (!$attribute instanceof ProductSelectAttribute) {
            return [];
        }

        $options = $attribute->getOptions();
        $customFields = [];
        $values = [];
        foreach ($options as $option) {
            foreach ($option->getName() as $language => $name) {
                $values[$language][] = $option->getCode();
            }
        }

        foreach ($values as $language => $value) {
            if (!$attribute instanceof ProductMultiSelectAttribute) {
                $value = $value[0];
            }

            if (!is_null($customField) && $customField->getType() === CustomFieldTypes::BOOL) {
                $value = YesNo::cast($value);
            }

            $customFields[IsoCodeConverter::ergonodeToShopwareIso($language)]['customFields'][$customFieldName] = $value;
        }

        return $customFields;
    }
}

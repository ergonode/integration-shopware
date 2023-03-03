<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Shopware\Core\Framework\Context;

use Shopware\Core\System\CustomField\CustomFieldTypes;

use function in_array;
use function sprintf;

class SimpleProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    private CustomFieldProvider $customFieldProvider;

    public function __construct(
        TranslationTransformer $translationTransformer,
        CustomFieldProvider $customFieldProvider
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->customFieldProvider = $customFieldProvider;
    }

    public function supports(array $node): bool
    {
        $type = AttributeTypesEnum::getNodeType($node['attribute']);

        return in_array($type, [
            AttributeTypesEnum::TEXT,
            AttributeTypesEnum::TEXTAREA,
            AttributeTypesEnum::SELECT,
            AttributeTypesEnum::MULTISELECT,
            AttributeTypesEnum::NUMERIC,
            AttributeTypesEnum::UNIT,
            AttributeTypesEnum::DATE,
        ]);
    }

    public function transformNode(array $node, string $customFieldName, Context $context): array
    {
        $customField = $this->customFieldProvider->getCustomFieldByName($customFieldName, $context);

        $translations = $this->getTranslatedValues($node['translations']);

        return $this->translationTransformer->transform(
            $translations,
            sprintf('customFields.%s', $customFieldName),
            !is_null($customField) && $customField->getType() === CustomFieldTypes::BOOL
        );

    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        foreach ($valueTranslations as &$valueTranslation) {
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);

            $translatedValue = null;
            switch($valueKey) {
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_ARRAY:
                        $translatedValue = $valueTranslation[$valueKey]['code'] ?? null;
                    break;
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_MULTI_ARRAY:
                    $translatedValue = array_column(
                        $valueTranslation[$valueKey],
                        'code'
                    );
                    break;
                default:
                    $translatedValue = $valueTranslation[$valueKey] ?? null;
                    break;
            }

            if ($translatedValue) {
                $valueTranslation['value'] = $translatedValue;
            }
        }

        return $valueTranslations;
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Provider\CustomFieldMappingProvider;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Ergonode\IntegrationShopware\Util\ErgonodeApiValueKeyResolverUtil;
use Shopware\Core\Framework\Context;

use function in_array;
use function sprintf;

class SimpleProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    private CustomFieldMappingProvider $customFieldMappingProvider;

    public function __construct(
        TranslationTransformer $translationTransformer,
        CustomFieldMappingProvider $customFieldMappingProvider
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->customFieldMappingProvider = $customFieldMappingProvider;
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
        $mapping = $this->customFieldMappingProvider->provideByShopwareKey($customFieldName, $context);

        $translations = $this->getTranslatedValues($node['translations']);

        return $this->translationTransformer->transform(
            $translations,
            sprintf('customFields.%s', $customFieldName),
            $mapping && $mapping->isCastToBool()
        );

    }

    private function getTranslatedValues(array $valueTranslations): array
    {
        foreach ($valueTranslations as &$valueTranslation) {
            $valueKey = ErgonodeApiValueKeyResolverUtil::resolve($valueTranslation['__typename']);
            switch($valueKey) {
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_ARRAY:
                    $translatedValue = $valueTranslation[$valueKey]['code'];
                    break;
                case ErgonodeApiValueKeyResolverUtil::TYPE_VALUE_MULTI_ARRAY:
                    $translatedValue = array_column(
                        $valueTranslation[$valueKey],
                        'code'
                    );
                    break;
                default:
                    $translatedValue = $valueTranslation[$valueKey];
                    break;
            }

            $valueTranslation['value'] = $translatedValue;
        }

        return $valueTranslations;
    }
}

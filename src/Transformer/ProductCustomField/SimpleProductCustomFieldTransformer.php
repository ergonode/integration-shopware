<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Shopware\Core\Framework\Context;

use function in_array;
use function sprintf;

class SimpleProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    public function __construct(
        TranslationTransformer $translationTransformer
    ) {
        $this->translationTransformer = $translationTransformer;
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
        return $this->translationTransformer->transform(
            $node['valueTranslations'],
            sprintf('customFields.%s', $customFieldName)
        );
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\Provider\CustomFieldProvider;
use Ergonode\IntegrationShopware\Resolver\CustomFieldTransformerResolver;
use Ergonode\IntegrationShopware\Util\CustomFieldUtil;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;

class CustomFieldTransformer
{
    private TranslationTransformer $translationTransformer;

    private CustomFieldProvider $customFieldProvider;

    private CustomFieldTransformerResolver $customFieldTransformerResolver;

    public function __construct(
        TranslationTransformer $translationTransformer,
        CustomFieldProvider $customFieldProvider,
        CustomFieldTransformerResolver $customFieldTransformerResolver
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->customFieldProvider = $customFieldProvider;
        $this->customFieldTransformerResolver = $customFieldTransformerResolver;
    }

    public function transformAttributeNode(array $node, Context $context): array
    {
        $code = $node['code'];

        $customField = $this->customFieldProvider->getErgonodeCustomFieldByName(
            CustomFieldUtil::buildCustomFieldName($code),
            $context
        );

        $typedTransformer = $this->customFieldTransformerResolver->resolve($node);

        if (!empty($node['name'])) {
            $label = $this->translationTransformer->transform($node['name']);
        }

        return array_merge_recursive(
            [
                'id' => $customField ? $customField->getId() : null,
                'name' => CustomFieldUtil::buildCustomFieldName($code),
                'config' => [
                    'label' => empty($label) ? [Defaults::LANGUAGE_SYSTEM => $code] : $label,
                ],
            ],
            $typedTransformer->transformNode($node)
        );
    }
}
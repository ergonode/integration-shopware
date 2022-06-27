<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Strix\Ergonode\Provider\CustomFieldProvider;
use Strix\Ergonode\Resolver\CustomFieldTransformerResolver;
use Strix\Ergonode\Util\Constants;
use Strix\Ergonode\Util\CustomFieldUtil;

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

        $customField = $this->customFieldProvider->getCustomFieldByName(
            CustomFieldUtil::buildCustomFieldName($code),
            $context
        );

        $typedTransformer = $this->customFieldTransformerResolver->resolve($node);

        if (!empty($node['label'])) {
            $label = $this->translationTransformer->transform($node['label']);
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
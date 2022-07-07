<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\ProductCustomField;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Enum\AttributeTypesEnum;
use Strix\Ergonode\Provider\ProductProvider;
use Strix\Ergonode\Transformer\TranslationTransformer;
use Strix\Ergonode\Util\CustomFieldUtil;

use function is_array;

class RelationProductCustomFieldTransformer implements ProductCustomFieldTransformerInterface
{
    private TranslationTransformer $translationTransformer;

    private ProductProvider $productProvider;

    public function __construct(
        TranslationTransformer $translationTransformer,
        ProductProvider $productProvider
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->productProvider = $productProvider;
    }

    public function supports(array $node): bool
    {
        return AttributeTypesEnum::RELATION === AttributeTypesEnum::getNodeType($node['attribute']);
    }

    public function transformNode(array $node, Context $context): array
    {
        $code = $node['attribute']['code'];

        $translated = $this->translationTransformer->transform(
            $node['valueTranslations']
        );

        foreach ($translated as &$value) {
            if (!is_array($value)) {
                $value = [];
                continue;
            }

            $ids = [];
            foreach ($value as $product) {
                if (isset($product['sku'])) {
                    $product = $this->productProvider->getProductBySku($product['sku'], $context);
                    if (null === $product) {
                        continue; // product might not exist at this point; for example will be created later
                    }

                    $ids[] = $product->getId();
                }
            }

            $value = [
                'customFields' => [
                    CustomFieldUtil::buildCustomFieldName($code) => $ids,
                ],
            ];
        }

        return $translated;
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Enum\AttributeTypesEnum;
use Ergonode\IntegrationShopware\Provider\ProductProvider;
use Ergonode\IntegrationShopware\Transformer\TranslationTransformer;
use Shopware\Core\Framework\Context;

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

    public function transformNode(array $node, string $customFieldName, Context $context): array
    {
        $translated = $this->translationTransformer->transform(
            $node['translations']
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
                    $customFieldName => $ids,
                ],
            ];
        }

        return $translated;
    }
}

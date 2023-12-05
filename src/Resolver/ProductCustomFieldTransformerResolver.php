<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Resolver;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Ergonode\IntegrationShopware\Transformer\ProductCustomField\ProductCustomFieldTransformerInterface;

class ProductCustomFieldTransformerResolver
{
    /**
     * @var iterable|ProductCustomFieldTransformerInterface[]
     */
    private iterable $transformers;

    public function __construct(
        iterable $transformers
    ) {
        $this->transformers = $transformers;
    }

    public function resolve(ProductAttribute $attribute): ?ProductCustomFieldTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($attribute)) {
                return $transformer;
            }
        }

        return null; // node type not supported
    }
}

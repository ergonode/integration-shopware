<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;

class ProductTransformerChain implements ProductDataTransformerInterface
{
    private array $transformers;

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    public function transform(array $productData, Context $context): array
    {
        /** @var ProductDataTransformerInterface $transformer */
        foreach ($this->transformers as $transformer) {
            $productData = $transformer->transform($productData, $context);
        }

        return $productData;
    }
}
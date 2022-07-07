<?php

declare(strict_types=1);

namespace Strix\Ergonode\Resolver;

use Strix\Ergonode\Transformer\ProductCustomField\ProductCustomFieldTransformerInterface;

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

    public function resolve(array $node): ?ProductCustomFieldTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($node)) {
                return $transformer;
            }
        }

        return null; // node type not supported
    }
}
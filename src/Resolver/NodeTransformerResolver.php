<?php

declare(strict_types=1);

namespace Strix\Ergonode\Resolver;

use Strix\Ergonode\Transformer\NodeTransformerInterface;

class NodeTransformerResolver
{
    /**
     * @var iterable|NodeTransformerInterface[]
     */
    private iterable $transformers;

    public function __construct(iterable $transformers)
    {
        $this->transformers = $transformers;
    }

    public function resolve(string $className): ?NodeTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($className)) {
                return $transformer;
            }
        }

        return null;
    }
}
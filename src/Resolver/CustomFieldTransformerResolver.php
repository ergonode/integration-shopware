<?php

declare(strict_types=1);

namespace Strix\Ergonode\Resolver;

use Strix\Ergonode\Transformer\CustomField\CustomFieldTransformerInterface;
use Strix\Ergonode\Transformer\CustomField\TextCustomFieldTransformer;

class CustomFieldTransformerResolver
{
    /**
     * @var iterable|CustomFieldTransformerInterface[]
     */
    private iterable $transformers;

    private TextCustomFieldTransformer $defaultTransformer;

    public function __construct(
        iterable $transformers,
        TextCustomFieldTransformer $textCustomFieldTransformer
    ) {
        $this->transformers = $transformers;
        $this->defaultTransformer = $textCustomFieldTransformer;
    }

    public function resolve(array $node): CustomFieldTransformerInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($node)) {
                return $transformer;
            }
        }

        return $this->defaultTransformer;
    }
}
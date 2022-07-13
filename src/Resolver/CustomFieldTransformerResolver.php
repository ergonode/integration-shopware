<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Resolver;

use Ergonode\IntegrationShopware\Transformer\CustomField\CustomFieldTransformerInterface;
use Ergonode\IntegrationShopware\Transformer\CustomField\TextCustomFieldTransformer;

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
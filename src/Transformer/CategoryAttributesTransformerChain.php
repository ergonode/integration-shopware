<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\CategoryTransformationDTO;
use Shopware\Core\Framework\Context;

class CategoryAttributesTransformerChain implements CategoryDataTransformerInterface
{
    private array $transformers;

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    public function transform(CategoryTransformationDTO $categoryData, Context $context): CategoryTransformationDTO
    {
        /** @var CategoryDataTransformerInterface $transformer */
        foreach ($this->transformers as $transformer) {
            $categoryData = $transformer->transform($categoryData, $context);
        }

        return $categoryData;
    }
}

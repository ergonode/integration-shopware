<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer;

use Ergonode\IntegrationShopware\DTO\ProductTransformationDTO;
use Shopware\Core\Framework\Context;

class ProductTransformerChain implements ProductDataTransformerInterface
{
    private array $transformers;

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO
    {
        /** @var ProductDataTransformerInterface $transformer */
        foreach ($this->transformers as $transformer) {
            $productData = $transformer->transform($productData, $context);
        }

        return $productData;
    }
}

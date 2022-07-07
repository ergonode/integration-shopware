<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\DTO\ProductTransformationDTO;

interface ProductDataTransformerInterface
{
    public function transform(ProductTransformationDTO $productData, Context $context): ProductTransformationDTO;
}
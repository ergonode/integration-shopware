<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;

interface ProductDataTransformerInterface
{
    public function transform(array $productData, Context $context): array;
}
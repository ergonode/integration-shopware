<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Product\Transformer;

use Strix\Ergonode\Modules\Product\Struct\ErgonodeDeletedProduct;
use Strix\Ergonode\Transformer\NodeTransformerInterface;

class DeletedProductNodeTransformer implements NodeTransformerInterface
{
    public function supports(string $className): bool
    {
        return $className === ErgonodeDeletedProduct::class;
    }

    public function transformNode(array $node): ErgonodeDeletedProduct
    {
        return new ErgonodeDeletedProduct($node['__value__']);
    }
}
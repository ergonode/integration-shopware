<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer;

use Shopware\Core\Framework\Context;

interface NodeTransformerInterface
{
    /**
     * Transforms Ergonode Node data to Shopware repository array (return value can be passed to create/update/upsert methods).
     */
    public function transformNode(array $node, Context $context): array;
}
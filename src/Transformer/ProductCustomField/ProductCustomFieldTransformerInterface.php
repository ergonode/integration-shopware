<?php

declare(strict_types=1);

namespace Strix\Ergonode\Transformer\ProductCustomField;

use Shopware\Core\Framework\Context;

interface ProductCustomFieldTransformerInterface
{
    public function supports(array $node): bool;

    public function transformNode(array $node, Context $context): array;
}
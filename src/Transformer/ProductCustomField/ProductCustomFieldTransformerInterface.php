<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Shopware\Core\Framework\Context;

interface ProductCustomFieldTransformerInterface
{
    public function supports(array $node): bool;

    public function transformNode(array $node, string $customFieldName, Context $context): array;
}
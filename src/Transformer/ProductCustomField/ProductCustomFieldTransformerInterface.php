<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Transformer\ProductCustomField;

use Ergonode\IntegrationShopware\Model\ProductAttribute;
use Shopware\Core\Framework\Context;

interface ProductCustomFieldTransformerInterface
{
    public function supports(ProductAttribute $attribute): bool;

    public function transformNode(array $node, string $customFieldName, Context $context): array;
}

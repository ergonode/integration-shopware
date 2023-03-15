<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor\Attribute;

use Shopware\Core\Framework\Context;

/**
 * Interface which handles attributes that require custom processing in Shopware
 */
interface AttributeCustomProcessorInterface
{
    public function isSupported(array $node, Context $context): bool;

    public function process(array $node, Context $context): void;
}

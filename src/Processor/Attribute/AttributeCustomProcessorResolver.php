<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor\Attribute;

use Shopware\Core\Framework\Context;

class AttributeCustomProcessorResolver
{
    /** @var AttributeCustomProcessorInterface[]  */
    private iterable $processors;

    public function __construct(iterable $processors = [])
    {
        $this->processors = $processors;
    }

    public function resolve(array $node, Context $context): ?AttributeCustomProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->isSupported($node, $context)) {
                return $processor;
            }
        }

        return null;
    }
}

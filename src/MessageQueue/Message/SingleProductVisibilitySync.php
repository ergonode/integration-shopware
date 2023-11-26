<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class SingleProductVisibilitySync implements AsyncMessageInterface
{
    private array $skuMap;

    public function __construct(array $skuMap)
    {
        $this->skuMap = $skuMap;
    }

    public function getSkuMap(): array
    {
        return $this->skuMap;
    }
}

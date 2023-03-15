<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Message;

class SingleProductSync
{
    private string $sku;

    public function __construct(string $sku)
    {
        $this->sku = $sku;
    }

    public function getSku(): string
    {
        return $this->sku;
    }
}

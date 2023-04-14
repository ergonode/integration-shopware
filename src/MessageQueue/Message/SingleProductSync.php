<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Message;

class SingleProductSync
{
    private string $sku;

    /**
     * Used when SingleProductSync is fired from ProductSync or from previous SingleProductSync.
     * Prevents overwriting categories added in previous run (when paginating over categoryList).
     */
    private bool $appendCategories;

    public function __construct(string $sku, bool $appendCategories = false)
    {
        $this->sku = $sku;
        $this->appendCategories = $appendCategories;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function shouldAppendCategories(): bool
    {
        return $this->appendCategories;
    }
}

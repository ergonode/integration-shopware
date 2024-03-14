<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Shopware\Core\Framework\Context;

interface ProductCategorySyncProcessorInterface
{
    public function process(string $sku, Context $context): SyncCounterDTO;
}
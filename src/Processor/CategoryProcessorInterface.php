<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Shopware\Core\Framework\Context;

interface CategoryProcessorInterface
{
    public function processStream(
        string $treeCode,
        Context $context,
        ?int $categoryCount = null
    ): SyncCounterDTO;
}
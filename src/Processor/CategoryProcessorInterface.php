<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Shopware\Core\Framework\Context;

interface CategoryProcessorInterface
{
    /**
     * @param string[] $treeCodes
     */
    public function processStream(
        array $treeCodes,
        Context $context
    ): SyncCounterDTO;

    public static function getDefaultPriority(): int;
}

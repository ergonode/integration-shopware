<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeSyncHistory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeSyncHistoryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeSyncHistoryEntity::class;
    }
}
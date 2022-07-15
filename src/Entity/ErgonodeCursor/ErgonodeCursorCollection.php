<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\ErgonodeCursor;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ErgonodeCursorCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ErgonodeCursorEntity::class;
    }
}
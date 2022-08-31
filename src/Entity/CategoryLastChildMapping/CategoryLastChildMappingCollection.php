<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class CategoryLastChildMappingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CategoryLastChildMappingEntity::class;
    }
}
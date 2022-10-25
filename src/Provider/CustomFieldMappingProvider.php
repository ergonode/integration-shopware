<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CustomFieldMappingProvider extends AttributeMappingProvider
{
    public function __construct(
        EntityRepositoryInterface $repository,
        CacheInterface $ergonodeAttributeMappingCache
    ) {
        parent::__construct($repository, $ergonodeAttributeMappingCache);
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider\Mapping;

use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;
use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Contracts\Cache\CacheInterface;

use function array_filter;
use function md5;

abstract class AbstractMappingProvider
{
    private const MAPPING_CACHE_KEY = 'mapping';

    private EntityRepositoryInterface $repository;

    private CacheInterface $cache;

    public function __construct(
        EntityRepositoryInterface $ergonodeAttributeMappingRepository,
        CacheInterface $ergonodeAttributeMappingCache
    ) {
        $this->repository = $ergonodeAttributeMappingRepository;
        $this->cache = $ergonodeAttributeMappingCache;
    }

    public function provideByShopwareKey(string $key, Context $context): ?ErgonodeAttributeMappingEntity
    {
        $map = $this->getAttributeMap($context);

        return $map[$key] ?? null;
    }

    public function provideByErgonodeKey(string $key, Context $context): ErgonodeAttributeMappingCollection
    {
        $map = $this->getAttributeMap($context);

        return new ErgonodeAttributeMappingCollection(
            array_filter(
                $map,
                fn(ErgonodeAttributeMappingEntity $entity) => $key === $entity->getErgonodeKey()
            )
        );
    }

    private function getAttributeMap(Context $context): array
    {
        $cacheKey = md5(self::MAPPING_CACHE_KEY);

        try {
            return $this->cache->get($cacheKey, function () use ($context) {
                $map = [];

                $result = $this->repository->search(new Criteria(), $context);
                /** @var ErgonodeAttributeMappingEntity $entity */
                foreach ($result->getEntities() as $entity) {
                    $map[$entity->getShopwareKey()] = $entity;
                }

                return $map;
            });
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping\ErgonodeCategoryAttributeMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryAttributeMapping\ErgonodeCategoryAttributeMappingEntity;
use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Contracts\Cache\CacheInterface;
use function array_filter;
use function md5;

class CategoryAttributeMappingProvider
{
    private EntityRepository $repository;

    private CacheInterface $cache;

    private string $mappingCacheKey;

    public function __construct(
        EntityRepository $repository,
        CacheInterface $ergonodeAttributeMappingCache
    ) {
        $this->repository = $repository;
        $this->cache = $ergonodeAttributeMappingCache;
        $this->mappingCacheKey = md5($this->repository->getDefinition()->getEntityName());
    }

    public function provideByShopwareKey(string $key, Context $context): ?ErgonodeCategoryAttributeMappingEntity
    {
        $map = $this->getAttributeMap($context);

        return $map[$key] ?? null;
    }

    public function provideByErgonodeKey(string $key, Context $context): ErgonodeCategoryAttributeMappingCollection
    {
        $map = $this->getAttributeMap($context);

        return new ErgonodeCategoryAttributeMappingCollection(
            array_filter(
                $map,
                fn(ErgonodeCategoryAttributeMappingEntity $entity) => $key === $entity->getErgonodeKey()
            )
        );
    }

    private function getAttributeMap(Context $context): array
    {
        try {
            return $this->cache->get($this->mappingCacheKey, function () use ($context) {
                $map = [];

                $result = $this->repository->search(new Criteria(), $context);

                /** @var ErgonodeCategoryAttributeMappingEntity $entity */
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

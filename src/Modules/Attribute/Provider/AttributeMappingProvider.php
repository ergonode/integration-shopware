<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingCollection;
use Strix\Ergonode\Modules\Attribute\Entity\ErgonodeAttributeMapping\ErgonodeAttributeMappingEntity;

class AttributeMappingProvider
{
    private EntityRepositoryInterface $repository;

    private ?array $mappingCache = null;

    public function __construct(
        EntityRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function provideByShopwareKey(string $key, Context $context): ?ErgonodeAttributeMappingEntity
    {
        if (null === $this->mappingCache) {
            $this->loadMappingEntities($context);
        }

        return $this->mappingCache[$key] ?? null;
    }

    public function provideByErgonodeKey(string $key, Context $context): ErgonodeAttributeMappingCollection
    {
        if (null === $this->mappingCache) {
            $this->loadMappingEntities($context);
        }

        return new ErgonodeAttributeMappingCollection(
            \array_filter(
                $this->mappingCache,
                fn(ErgonodeAttributeMappingEntity $entity) => $key === $entity->getErgonodeKey()
            )
        );
    }

    private function loadMappingEntities(Context $context): void
    {
        $this->mappingCache = [];
        $result = $this->repository->search(new Criteria(), $context);

        /** @var ErgonodeAttributeMappingEntity $entity */
        foreach ($result->getEntities() as $entity) {
            $this->mappingCache[$entity->getShopwareKey()] = $entity;
        }
    }
}
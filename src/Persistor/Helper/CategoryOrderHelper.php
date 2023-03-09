<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor\Helper;

use Doctrine\DBAL\Connection;
use Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping\CategoryLastChildMappingCollection;
use Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping\CategoryLastChildMappingDefinition;
use Ergonode\IntegrationShopware\Entity\CategoryLastChildMapping\CategoryLastChildMappingEntity;
use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class CategoryOrderHelper
{
    private array $lastCategoryMapping = [];

    private EntityRepository $repository;

    private Connection $connection;

    private EntityRepository $categoryRepository;

    public function __construct(
        EntityRepository $categoryLastChildMappingRepository,
        Connection $connection,
        EntityRepository $categoryRepository
    ) {
        $this->repository = $categoryLastChildMappingRepository;
        $this->connection = $connection;
        $this->categoryRepository = $categoryRepository;
    }

    public function getLastCategoryIdForParent(?string $parentCategoryId): ?string
    {
        return $this->lastCategoryMapping[$parentCategoryId]['lastChildId'] ?? null;
    }

    public function load(array $ids, Context $context): void
    {
        $this->reset();

        if (empty($ids)) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter('OR',
                [
                    new EqualsAnyFilter('categoryId', $ids),
                    new EqualsFilter('categoryId', null)
                ]
            )
        );

        /** @var CategoryLastChildMappingCollection $collection */
        $collection = $this->repository->search($criteria, $context)->getEntities();

        /** @var CategoryLastChildMappingEntity $entity */
        foreach ($collection as $entity) {
            $this->lastCategoryMapping[$entity->getCategoryId()] = [
                'mappingId' => $entity->getId(),
                'lastChildId' => $entity->getLastChildId()
            ];
        }
    }

    public function set(?string $parentId, string $childId): void
    {
        $this->lastCategoryMapping[$parentId]['lastChildId'] = $childId;
    }

    public function has(string $parentId): bool
    {
        return \array_key_exists($parentId, $this->lastCategoryMapping);
    }

    public function reset(): void
    {
        $this->lastCategoryMapping = [];
    }

    public function save(Context $context): void
    {
        $payload = [];
        foreach ($this->lastCategoryMapping as $parentId => $mappingData) {
            $payload[] = [
                'id' => $mappingData['mappingId'] ?? null,
                'categoryId' => $parentId,
                'lastChildId' => $mappingData['lastChildId']
            ];
        }

        $this->repository->upsert($payload, $context);
    }

    public function clearSaved(): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'DELETE FROM %s;',
                CategoryLastChildMappingDefinition::ENTITY_NAME
            )
        );
    }

    public function getLastRootCategoryId(Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new AndFilter(
            [
                new EqualsFilter('parentId', NULL),
                new EqualsFilter('afterCategoryId', NULL)
            ]
        ));
        $criteria->addSorting(new FieldSorting('autoIncrement', FieldSorting::ASCENDING));
        $criteria->setLimit(1);
        $res = $this->categoryRepository->searchIds($criteria, $context);

        return $res->firstId();
    }
}

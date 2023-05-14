<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Persistor\Helper;

use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMapping\ErgonodeCategoryMappingCollection;
use Ergonode\IntegrationShopware\Entity\ErgonodeCategoryMappingExtension\ErgonodeCategoryMappingExtensionEntity;
use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Ergonode\IntegrationShopware\Provider\CategoryProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class ExistingCategoriesHelper
{
    private array $existingCategories = [];

    private CategoryProvider $categoryProvider;

    private EntityRepositoryInterface $ergonodeCategoryMappingRepository;

    public function __construct(CategoryProvider $categoryProvider, EntityRepositoryInterface $ergonodeCategoryMappingRepository)
    {
        $this->categoryProvider = $categoryProvider;
        $this->ergonodeCategoryMappingRepository = $ergonodeCategoryMappingRepository;
    }

    private function getEntityExtensionCode(Entity $entity): ?string
    {
        $extension = $entity->getExtension(ErgonodeCategoryMappingExtension::EXTENSION_NAME);
        if ($extension instanceof ErgonodeCategoryMappingExtensionEntity) {
            return $extension->getCode();
        }

        return null;
    }

    public function load(array $codes, Context $context): void
    {
        $this->reset();
        $entities = $this->categoryProvider->getCategoriesWithAnyCode($codes, $context, [
            ErgonodeCategoryMappingExtension::EXTENSION_NAME
        ]);

        foreach ($entities as $entity) {
            $this->existingCategories[$this->getEntityExtensionCode($entity)] = $entity->getId();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('ergonodeKey', $codes));

        /** @var ErgonodeCategoryMappingCollection $result */
        $result = $this->ergonodeCategoryMappingRepository->search($criteria, $context)->getEntities();
        // overwrite existing categories with mappings
        foreach ($result as $row) {
            $this->existingCategories[$row->getErgonodeKey()] = $row->getShopwareId();
        }
    }

    public function get(string $code): ?string
    {
        return $this->existingCategories[$code] ?? null;
    }

    public function set(string $code, string $id): void
    {
        $this->existingCategories[$code] = $id;
    }

    public function has(string $code): bool
    {
        return \array_key_exists($code, $this->existingCategories);
    }

    public function reset(): void
    {
        $this->existingCategories = [];
    }
}

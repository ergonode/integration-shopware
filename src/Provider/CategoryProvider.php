<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;

class CategoryProvider
{
    private EntityRepository $categoryRepository;

    public function __construct(
        EntityRepository $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoryByMapping(
        string $code,
        ?string $locale,
        Context $context,
        array $associations = []
    ): ?CategoryEntity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.code', $code));
        $criteria->addFilter(new EqualsFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.locale', $locale));
        $criteria->addAssociations($associations);

        return $this->categoryRepository->search($criteria, $context)->first();
    }

    public function getCategoriesByCode(string $code, Context $context, array $associations = []): CategoryCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.code', $code));
        $criteria->addAssociations($associations);

        $result = $this->categoryRepository->search($criteria, $context)->getEntities();
        if (!$result instanceof CategoryCollection) {
            throw new \RuntimeException('Invalid category collection');
        }

        return $result;
    }

    public function getCategoryIdsNotInArray(array $notIn, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new NotFilter(
                NotFilter::CONNECTION_OR,
                [
                    new EqualsAnyFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.code', $notIn)
                ]
            )
        );

        return $this->categoryRepository->searchIds($criteria, $context)->getIds();
    }

    public function getCategoryIdsByCodes(array $codes, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.code', $codes)
        );

        return $this->categoryRepository->searchIds($criteria, $context)->getIds();
    }

    public function getCategoriesWithAnyCode(
        array $codes,
        Context $context,
        array $associations = []
    ): CategoryCollection {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter(ErgonodeCategoryMappingExtension::EXTENSION_NAME . '.code', $codes));
        $criteria->addAssociations($associations);

        $result = $this->categoryRepository->search($criteria, $context)->getEntities();
        if (!$result instanceof CategoryCollection) {
            throw new \RuntimeException('Invalid category collection');
        }

        return $result;
    }
}

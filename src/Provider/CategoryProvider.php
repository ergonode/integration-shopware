<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Strix\Ergonode\Extension\ErgonodeCategoryMappingExtension;

class CategoryProvider
{
    private EntityRepositoryInterface $categoryRepository;

    public function __construct(
        EntityRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoryByMapping(
        string $code,
        string $locale,
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

        return $this->categoryRepository->search($criteria, $context)->getEntities();
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_filter;
use function array_values;

class DeleteCategoryExtensionSubscriber implements EventSubscriberInterface
{
    private EntityRepository $ergonodeCategoryMappingExtensionRepository;

    private EntityRepository $categoryRepository;

    private EntityRepository $ergonodeCategoryMappingRepository;

    public function __construct(
        EntityRepository $ergonodeCategoryMappingExtensionRepository,
        EntityRepository $categoryRepository,
        EntityRepository $ergonodeCategoryMappingRepository
    ) {
        $this->ergonodeCategoryMappingExtensionRepository = $ergonodeCategoryMappingExtensionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->ergonodeCategoryMappingRepository = $ergonodeCategoryMappingRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $categories = $this->getAllDeletedCategories($event);
        if (0 === $categories->count()) {
            return;
        }

        $this->deleteMappings($categories, $event->getContext());

        $deletePayloads = $this->buildCategoryExtensionDeletePayloads($categories);
        if (empty($deletePayloads)) {
            return;
        }


        $context = $event->getContext();
        $event->addSuccess(function () use ($deletePayloads, $context) {
            $this->ergonodeCategoryMappingExtensionRepository->delete(array_values($deletePayloads), $context);
        });
    }

    private function getAllDeletedCategories(BeforeDeleteEvent $event): CategoryCollection
    {
        $ids = $event->getIds(CategoryDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return new CategoryCollection();
        }

        $criteria = new Criteria();
        $criteria->addAssociation('children');
        $orFilter = new OrFilter([
            new EqualsAnyFilter('id', $ids) // include deleted parents
        ]);
        foreach ($ids as $id) {
            $orFilter->addQuery(new ContainsFilter('path', $id)); // look for all descendants
        }
        $criteria->addFilter($orFilter);

        /** @var CategoryCollection $categories */
        $categories = $this->categoryRepository->search($criteria, $event->getContext())->getEntities();

        return $categories;
    }

    private function buildCategoryExtensionDeletePayloads(CategoryCollection $categories): array
    {
        return array_values(
            array_filter(
                $categories->map(static function (Entity $entity) {
                    $extensionId = $entity->get(ErgonodeCategoryMappingExtension::PROPERTY_NAME);
                    if (null === $extensionId) {
                        return false;
                    }

                    return [
                        'id' => $extensionId,
                    ];
                })
            )
        );
    }

    private function deleteMappings(CategoryCollection $categories, Context $context)
    {
        $categoryIds = array_values(
            array_filter(
                $categories->map(static function (CategoryEntity $entity) {
                    return $entity->getId();
                })
            )
        );
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('shopwareId', $categoryIds));
        $mappingIds = $this->ergonodeCategoryMappingRepository->searchIds($criteria, $context);
        if (!empty($mappingIds->getIds())) {
            $this->ergonodeCategoryMappingRepository->delete([array_values($mappingIds->getIds())], $context);
        }
    }
}

<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Extension\ErgonodeCategoryMappingExtension;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_filter;
use function array_merge;
use function array_search;
use function array_slice;
use function array_values;
use function explode;
use function reset;
use function trim;

class DeleteCategoryExtensionSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $ergonodeCategoryMappingExtensionRepository;

    private EntityRepositoryInterface $categoryRepository;

    public function __construct(
        EntityRepositoryInterface $ergonodeCategoryMappingExtensionRepository,
        EntityRepositoryInterface $categoryRepository
    ) {
        $this->ergonodeCategoryMappingExtensionRepository = $ergonodeCategoryMappingExtensionRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $ids = $this->getAllDeletedCategoryIds($event);
        if (empty($ids)) {
            return;
        }

        $deletePayloads = $this->getCategoryExtensionDeletePayloads($ids, $event->getContext());
        if (empty($deletePayloads)) {
            return;
        }

        $context = $event->getContext();
        $event->addSuccess(function () use ($deletePayloads, $context) {
            $this->ergonodeCategoryMappingExtensionRepository->delete(array_values($deletePayloads), $context);
        });
    }

    /**
     * @return string[]
     */
    private function getAllDeletedCategoryIds(BeforeDeleteEvent $event): array
    {
        $ids = $event->getIds(CategoryDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return [];
        }

        $paths = $this->getCategoryPaths($ids, $event->getContext());

        $mainId = reset($ids);

        foreach ($paths as &$path) {
            $fromIndex = array_search($mainId, $path);
            if (false === $fromIndex) {
                $path = [];
            }

            $path = array_slice($path, $fromIndex + 1);
        }

        return array_merge(
            $ids,
            ...array_values($paths)
        );
    }

    private function getCategoryPaths(array $categoryIds, Context $context): array
    {
        return array_filter(
            $this->categoryRepository
                ->search(new Criteria($categoryIds), $context)
                ->map(static function (CategoryEntity $category) {
                    $path = $category->getPath();
                    if (null === $path) {
                        return false;
                    }

                    $pathStr = trim($path, '|');

                    return explode('|', $pathStr);
                })
        );
    }

    private function getCategoryExtensionDeletePayloads(array $ids, Context $context): array
    {
        return array_values(
            array_filter(
                $this->categoryRepository
                    ->search(new Criteria($ids), $context)
                    ->map(static function (Entity $entity) {
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
}
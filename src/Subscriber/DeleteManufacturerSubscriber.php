<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_values;

class DeleteManufacturerSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $ergonodeMappingExtensionRepository;

    public function __construct(
        EntityRepositoryInterface $ergonodeMappingExtensionRepository
    ) {
        $this->ergonodeMappingExtensionRepository = $ergonodeMappingExtensionRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $ids = $event->getIds(ProductManufacturerDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return;
        }
        $context = $event->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $ids));
        $mappingIds = $this->ergonodeMappingExtensionRepository->searchIds($criteria, $context);
        if (!empty($mappingIds->getIds())) {
            $this->ergonodeMappingExtensionRepository->delete(array_values($mappingIds->getData()), $context);
        }
    }
}

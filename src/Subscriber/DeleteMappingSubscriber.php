<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\DeliveryTime\DeliveryTimeDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_values;

class DeleteMappingSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_ENTITIES = [
        ProductManufacturerDefinition::ENTITY_NAME,
        DeliveryTimeDefinition::ENTITY_NAME,
    ];

    private EntityRepository $ergonodeMappingExtensionRepository;

    public function __construct(
        EntityRepository $ergonodeMappingExtensionRepository
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
        foreach (self::SUPPORTED_ENTITIES as $entityName) {
            $ids = $event->getIds($entityName);
            if (empty($ids)) {
                continue;
            }
            $ids = $event->getIds($entityName);
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
}

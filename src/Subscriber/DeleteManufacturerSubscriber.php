<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_values;

class DeleteManufacturerSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $ergonodeMappingExtensionRepository;

    private EntityRepositoryInterface $manufacturerRepository;

    public function __construct(
        EntityRepositoryInterface $manufacturerRepository,
        EntityRepositoryInterface $ergonodeMappingExtensionRepository
    ) {
        $this->ergonodeMappingExtensionRepository = $ergonodeMappingExtensionRepository;
        $this->manufacturerRepository = $manufacturerRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $manufacturers = $this->getAllDeletedManufacturer($event);
        if (0 === $manufacturers->count()) {
            return;
        }

        $this->deleteMappings($manufacturers, $event->getContext());
    }

    private function getAllDeletedManufacturer(BeforeDeleteEvent $event): ProductManufacturerCollection
    {
        $ids = $event->getIds(ProductManufacturerDefinition::ENTITY_NAME);
        if (empty($ids)) {
            return new ProductManufacturerCollection();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $ids));

        /** @var ProductManufacturerCollection $manufacturers */
        $manufacturers = $this->manufacturerRepository->search($criteria, $event->getContext())->getEntities();

        return $manufacturers;
    }

    private function deleteMappings(ProductManufacturerCollection $manufacturers, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $manufacturers->getIds()));
        $mappingIds = $this->ergonodeMappingExtensionRepository->searchIds($criteria, $context);
        if (!empty($mappingIds->getIds())) {
            $this->ergonodeMappingExtensionRepository->delete(array_values($mappingIds->getData()), $context);
        }
    }
}

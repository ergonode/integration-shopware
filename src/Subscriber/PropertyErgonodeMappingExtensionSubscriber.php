<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PropertyErgonodeMappingExtensionSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $strixErgonodeMappingExtensionRepository;
    private EntityRepositoryInterface $propertyGroupRepository;
    private EntityRepositoryInterface $propertyGroupOptionRepository;

    public function __construct(
        EntityRepositoryInterface $strixErgonodeMappingExtensionRepository,
        EntityRepositoryInterface $propertyGroupRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository
    ) {
        $this->strixErgonodeMappingExtensionRepository = $strixErgonodeMappingExtensionRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $propertyGroupIds = $event->getIds(PropertyGroupDefinition::ENTITY_NAME);
        $propertyGroupOptionIds = $event->getIds(PropertyGroupOptionDefinition::ENTITY_NAME);

        if (empty($propertyGroupIds) && empty($propertyGroupOptionIds)) {
            return;
        }

        $extensionIds = [];
        if (false == empty($propertyGroupIds)) {
            $extensionIds = \array_merge(
                $extensionIds,
                $this->propertyGroupRepository->search(
                    new Criteria($propertyGroupIds),
                    $event->getContext()
                )->map(
                    fn(PropertyGroupEntity $entity) => [
                        'id' => $entity->get(AbstractErgonodeMappingExtension::PROPERTY_NAME)
                    ]
                )
            );
        }

        if (false == empty($propertyGroupOptionIds)) {
            $extensionIds = \array_merge(
                $extensionIds,
                $this->propertyGroupOptionRepository->search(
                    new Criteria($propertyGroupOptionIds),
                    $event->getContext()
                )->map(
                    fn(PropertyGroupOptionEntity $entity) => [
                        'id' => $entity->get(AbstractErgonodeMappingExtension::PROPERTY_NAME)
                    ]
                )
            );
        }

        if (empty($extensionIds)) {
            return;
        }

        $this->strixErgonodeMappingExtensionRepository->delete(\array_values($extensionIds), $event->getContext());
    }
}
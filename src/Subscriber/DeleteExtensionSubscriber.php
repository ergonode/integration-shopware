<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Extension\AbstractErgonodeMappingExtension;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_filter;
use function array_merge;
use function array_values;

class DeleteExtensionSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_ENTITIES = [
        PropertyGroupDefinition::ENTITY_NAME,
        PropertyGroupOptionDefinition::ENTITY_NAME,
        ProductCrossSellingDefinition::ENTITY_NAME,
    ];

    private EntityRepositoryInterface $ergonodeMappingExtensionRepository;

    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    public function __construct(
        EntityRepositoryInterface $ergonodeMappingExtensionRepository,
        DefinitionInstanceRegistry $definitionInstanceRegistry
    ) {
        $this->ergonodeMappingExtensionRepository = $ergonodeMappingExtensionRepository;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'onEntityBeforeDelete',
        ];
    }

    public function onEntityBeforeDelete(BeforeDeleteEvent $event): void
    {
        $entityExtensionDeletePayloads = [];

        foreach (self::SUPPORTED_ENTITIES as $entityName) {
            $ids = $event->getIds($entityName);
            if (empty($ids)) {
                continue;
            }

            $payloads = $this->getExtensionDeletePayloads($entityName, $ids, $event->getContext());
            if (empty($payloads)) {
                continue;
            }

            $entityExtensionDeletePayloads[] = $this->getExtensionDeletePayloads($entityName, $ids, $event->getContext());
        }

        if (empty($entityExtensionDeletePayloads)) {
            return;
        }

        $entityExtensionDeletePayloads = array_merge(...$entityExtensionDeletePayloads);

        $context = $event->getContext();
        $event->addSuccess(function () use ($entityExtensionDeletePayloads, $context) {
            $this->ergonodeMappingExtensionRepository->delete($entityExtensionDeletePayloads, $context);
        });
    }

    private function getExtensionDeletePayloads(string $entityName, array $ids, Context $context): array
    {
        $repository = $this->definitionInstanceRegistry->getRepository($entityName);

        return array_values(
            array_filter(
                $repository->search(new Criteria($ids), $context)
                    ->map(static function (Entity $entity) {
                        $extensionId = $entity->get(AbstractErgonodeMappingExtension::PROPERTY_NAME);
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
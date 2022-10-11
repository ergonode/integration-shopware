<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErgonodeMappingCacheSubscriber implements EventSubscriberInterface
{
    private AttributeMappingProvider $attributeMappingProvider;

    private AttributeMappingProvider $customFieldMappingProvider;

    public function __construct(
        AttributeMappingProvider $attributeMappingProvider,
        AttributeMappingProvider $customFieldMappingProvider
    ) {
        $this->attributeMappingProvider = $attributeMappingProvider;
        $this->customFieldMappingProvider = $customFieldMappingProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'ergonode_attribute_mapping.written' => 'onAttributeMappingChanged',
            'ergonode_attribute_mapping.deleted' => 'onAttributeMappingChanged',
            'ergonode_custom_field_mapping.written' => 'onCustomFieldMappingChanged',
            'ergonode_custom_field_mapping.deleted' => 'onCustomFieldMappingChanged',
        ];
    }

    public function onAttributeMappingChanged(): void
    {
        $this->attributeMappingProvider->invalidateCache();
    }

    public function onCustomFieldMappingChanged(): void
    {
        $this->customFieldMappingProvider->invalidateCache();
    }
}

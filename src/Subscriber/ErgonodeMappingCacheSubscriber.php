<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Provider\AttributeMappingProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErgonodeMappingCacheSubscriber implements EventSubscriberInterface
{
    private AttributeMappingProvider $attributeMappingProvider;

    public function __construct(AttributeMappingProvider $attributeMappingProvider)
    {
        $this->attributeMappingProvider = $attributeMappingProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'ergonode_attribute_mapping.written' => 'onMappingChanged',
            'ergonode_attribute_mapping.deleted' => 'onMappingChanged',
        ];
    }

    public function onMappingChanged(): void
    {
        $this->attributeMappingProvider->invalidateCache();
    }
}

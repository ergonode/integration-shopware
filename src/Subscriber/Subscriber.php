<?php

namespace Strix\Ergonode\Subscriber;

use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Subscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender',
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     */
    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        dump('StrixErgonode app loaded');
    }
}

<?php

namespace Strix\Ergonode\Subscriber;

use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Page\Product\ProductPage;
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
        // todo remove

        dump('StrixErgonode app loaded');

        $params = $event->getParameters();
        $page = $params['page'] ?? null;

        if ($page instanceof ProductPage) {
            dump($page->getProduct());
        }
    }
}

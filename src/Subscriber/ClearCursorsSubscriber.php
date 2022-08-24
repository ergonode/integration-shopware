<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\Event\BeforeSystemConfigChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearCursorsSubscriber implements EventSubscriberInterface
{
    private ErgonodeCursorManager $cursorManager;

    private ConfigProvider $configProvider;

    public function __construct(ErgonodeCursorManager $cursorManager, ConfigProvider $configProvider)
    {
        $this->cursorManager = $cursorManager;
        $this->configProvider = $configProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeSystemConfigChangedEvent::class => 'onBeforeSystemConfigChanged',
        ];
    }

    public function onBeforeSystemConfigChanged(BeforeSystemConfigChangedEvent $event): void
    {
        if (
            ConfigProvider::API_ENDPOINT_CONFIG !== $event->getKey() ||
            $this->configProvider->getErgonodeApiEndpoint() === $event->getValue()
        ) {
            return;
        }

        $this->cursorManager->deleteCursors(
            [],
            new Context(new SystemSource())
        );
    }
}
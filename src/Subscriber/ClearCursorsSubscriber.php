<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Ergonode\IntegrationShopware\Manager\ErgonodeCursorManager;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\Event\BeforeSystemConfigChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearCursorsSubscriber implements EventSubscriberInterface
{
    private ErgonodeCursorManager $cursorManager;

    private ConfigService $configService;

    public function __construct(ErgonodeCursorManager $cursorManager, ConfigService $configService)
    {
        $this->cursorManager = $cursorManager;
        $this->configService = $configService;
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
            configService::API_ENDPOINT_CONFIG !== $event->getKey() ||
            $this->configService->getErgonodeApiEndpoint() === $event->getValue()
        ) {
            return;
        }

        $this->cursorManager->deleteCursors(
            [],
            new Context(new SystemSource())
        );
    }
}

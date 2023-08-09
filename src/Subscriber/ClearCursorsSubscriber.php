<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Doctrine\DBAL\Connection;
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

    private Connection $connection;

    public function __construct(ErgonodeCursorManager $cursorManager, ConfigService $configService, Connection $connection)
    {
        $this->cursorManager = $cursorManager;
        $this->configService = $configService;
        $this->connection = $connection;
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

        $result = $this->connection->fetchOne('SHOW TABLES LIKE \'ergonode_cursor\';');
        if ($result !== false) {
            $this->cursorManager->deleteCursors(
                [],
                new Context(new SystemSource())
            );
        }
    }
}

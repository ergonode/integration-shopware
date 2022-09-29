<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Subscriber;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use Shopware\Core\Framework\Plugin\Event\PluginPostUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @todo Remove when Shopware fix their issue https://issues.shopware.com/issues/NEXT-14664 */
class UpdatePluginSubscriber implements EventSubscriberInterface
{
    private const COMPOSER_NAME = 'ergonode/integration-shopware';

    private TaskRegistry $registry;

    public function __construct(TaskRegistry $registry)
    {
        $this->registry = $registry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostUpdateEvent::class => 'afterPluginStateChange',
        ];
    }

    public function afterPluginStateChange(PluginPostUpdateEvent $event): void
    {
        if ($event->getPlugin()->getComposerName() === self::COMPOSER_NAME) {
            $this->registry->registerTasks();
        }
    }
}

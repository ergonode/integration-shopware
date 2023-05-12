<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\MessageQueue\Message\AttributeSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\CategorySync;
use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedAttributeSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedProductSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\LanguageSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\ProductSync;
use Ergonode\IntegrationShopware\MessageQueue\Message\ProductVisibilitySync;
use Ergonode\IntegrationShopware\Service\ConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\MessageBusInterface;

class FullSyncTaskHandler extends ScheduledTaskHandler
{
    private ConfigService $configService;

    private MessageBusInterface $messageBus;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ConfigService $configService,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->configService = $configService;
        $this->messageBus = $messageBus;
    }

    public static function getHandledMessages(): iterable
    {
        return [FullSyncTask::class];
    }

    public function run(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->messageBus->dispatch(new LanguageSync());
        $this->messageBus->dispatch(new AttributeSync());
        $this->messageBus->dispatch(new CategorySync());
        $this->messageBus->dispatch(new ProductSync());
        //$this->messageBus->dispatch(new ProductVisibilitySync());
        $this->messageBus->dispatch(new DeletedProductSync());
        $this->messageBus->dispatch(new DeletedAttributeSync());

        $this->configService->setLastFullSyncDatetime(
            new \DateTime('now', new \DateTimeZone($this->configService->getSchedulerStartTimezone()))
        );
    }

    private function shouldRun(): bool
    {
        if (!$this->configService->isSchedulerEnabled()) {
            return false;
        }

        $startDate = $this->configService->getSchedulerStartDatetime();
        $startTimezone = $this->configService->getSchedulerStartTimezone();
        if (!$startDate || !$startTimezone) {
            return false;
        }

        $currentDate = new \DateTime('now', new \DateTimeZone($startTimezone));

        $lastRun = $this->configService->getLastFullSyncDatetime();
        if (!$lastRun) {
            return $currentDate >= $startDate;
        }

        $currentDate->sub(
            \DateInterval::createFromDateString(
                sprintf('%s hours', $this->configService->getSchedulerRecurrenceHour())
            )
        );
        $currentDate->sub(
            \DateInterval::createFromDateString(
                sprintf('%s minutes', $this->configService->getSchedulerRecurrenceMinute())
            )
        );

        return $currentDate >= $lastRun;
    }
}

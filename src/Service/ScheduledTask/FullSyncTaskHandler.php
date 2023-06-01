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
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FullSyncTaskHandler
{
    private ConfigService $configService;

    private MessageBusInterface $messageBus;

    public function __construct(
        ConfigService $configService,
        MessageBusInterface $messageBus
    ) {
        $this->configService = $configService;
        $this->messageBus = $messageBus;
    }

    public function __invoke(FullSyncTask $message): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->messageBus->dispatch(new LanguageSync());
        $this->messageBus->dispatch(new AttributeSync());
        $this->messageBus->dispatch(new CategorySync());
        $this->messageBus->dispatch(new ProductSync());
        $this->messageBus->dispatch(new ProductVisibilitySync());
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

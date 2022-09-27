<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{
    public const CONFIG_NAMESPACE = 'ErgonodeIntegrationShopware.config.';

    public const API_ENDPOINT_CONFIG = self::CONFIG_NAMESPACE . 'ergonodeApiEndpoint';

    private SystemConfigService $configService;

    private EntityRepositoryInterface $salesChannelRepository;

    public function __construct(
        SystemConfigService $configService,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->configService = $configService;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @return ErgonodeAccessData[]
     */
    public function getSalesChannelErgonodeAccessData(Context $context): array
    {
        $salesChannels = $this->salesChannelRepository->search(new Criteria(), $context)->getEntities();
        $accessData = [];

        /** @var SalesChannelEntity $salesChannel */
        foreach ($salesChannels as $salesChannel) {
            $accessData[] = $this->getErgonodeAccessData($salesChannel->getId());
        }

        return $accessData;
    }

    public function getErgonodeApiEndpoint(): string
    {
        return $this->configService->getString(self::API_ENDPOINT_CONFIG); // always use global value
    }

    public function getErgonodeAccessData(?string $salesChannelId = null): ErgonodeAccessData
    {
        return new ErgonodeAccessData(
            $this->getErgonodeApiEndpoint(),
            $this->configService->getString(self::CONFIG_NAMESPACE . 'ergonodeApiKey', $salesChannelId),
            $salesChannelId
        );
    }

    public function getErgonodeCustomFieldKeys(): array
    {
        $keys = $this->configService->get(self::CONFIG_NAMESPACE . 'customFieldKeys');
        if (is_array($keys)) {
            return $keys;
        }

        return [];
    }

    public function getErgonodeCrossSellingKeys(): array
    {
        $keys = $this->configService->get(self::CONFIG_NAMESPACE . 'crossSellingKeys');
        if (is_array($keys)) {
            return $keys;
        }

        return [];
    }

    public function getCategoryTreeCode(): string
    {
        return $this->configService->getString(self::CONFIG_NAMESPACE . 'categoryTreeCode');
    }

    public function isSchedulerEnabled(): bool
    {
        return $this->configService->getBool(self::CONFIG_NAMESPACE.'schedulerEnabled');
    }

    public function getSchedulerStartDatetime(): ?\DateTime
    {
        $value = $this->configService->getString(self::CONFIG_NAMESPACE.'schedulerStartDatetime');

        return empty($value) ? null : new \DateTime($value);
    }

    public function getSchedulerRecurrenceHour(): string
    {
        return $this->configService->getString(self::CONFIG_NAMESPACE.'schedulerRecurrenceHour');
    }

    public function getSchedulerRecurrenceMinute(): string
    {
        return $this->configService->getString(self::CONFIG_NAMESPACE.'schedulerRecurrenceMinute');
    }

    public function getLastFullSyncDatetime(): ?\DateTime
    {
        $value = $this->configService->getString(self::CONFIG_NAMESPACE.'fullSyncDate');

        return empty($value) ? null : new \DateTime($value);
    }

    public function setLastFullSyncDatetime(\DateTime $date): void
    {
        $this->configService->set(self::CONFIG_NAMESPACE.'fullSyncDate', $date->format('d-m-Y H:i:s'));
    }

    public function getLastCategorySyncTimestamp(): int
    {
        $lastCheckedStr = $this->configService->getString(
            self::CONFIG_NAMESPACE . 'lastCategorySyncTime'
        );

        if (empty($lastCheckedStr)) {
            return 0;
        }

        return (new \DateTime($lastCheckedStr))->getTimestamp();
    }

    /**
     * @return string Human-readable time
     */
    public function setLastCategorySyncTimestamp(int $timestamp): string
    {
        $formatted = (new \DateTime('@' . $timestamp))->format(\DateTimeInterface::ATOM);
        $this->configService->set(
            self::CONFIG_NAMESPACE . 'lastCategorySyncTime',
            $formatted
        );

        return $formatted;
    }
}

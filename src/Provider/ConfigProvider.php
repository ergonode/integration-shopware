<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Provider;

use Ergonode\IntegrationShopware\Api\ErgonodeAccessData;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigProvider
{
    private const STRIX_ERGONODE_CONFIG_NAMESPACE = 'StrixErgonode.config.';

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

    public function getErgonodeAccessData(?string $salesChannelId = null): ErgonodeAccessData
    {
        return new ErgonodeAccessData(
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeBaseUrl'), // always use global url
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeApiKey', $salesChannelId),
            $salesChannelId
        );
    }

    public function getErgonodeCustomFields(): array
    {
        $keys = $this->configService->get(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'customFieldKeys');
        if (is_array($keys)) {
            return $keys;
        }

        return [];
    }

    public function getCategoryTreeCode(): string
    {
        return $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'categoryTreeCode');
    }
}
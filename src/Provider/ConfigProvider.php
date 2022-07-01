<?php

declare(strict_types=1);

namespace Strix\Ergonode\Provider;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigCollection;
use Shopware\Core\System\SystemConfig\SystemConfigEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Strix\Ergonode\Api\ErgonodeAccessData;

class ConfigProvider
{
    private const STRIX_ERGONODE_CONFIG_NAMESPACE = 'StrixErgonode.config.';

    private SystemConfigService $configService;

    private EntityRepositoryInterface $systemConfigRepository;

    public function __construct(
        SystemConfigService $configService,
        EntityRepositoryInterface $systemConfigRepository
    ) {
        $this->configService = $configService;
        $this->systemConfigRepository = $systemConfigRepository;
    }

    /**
     * @return ErgonodeAccessData[]
     */
    public function getAllErgonodeAccessData(Context $context): array
    {
        $defaultConfig = $this->getErgonodeAccessData();
        $baseUrl = $defaultConfig->getBaseUrl();

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('configurationKey', self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeApiKey')
        );

        /** @var SystemConfigCollection $result */
        $result = $this->systemConfigRepository->search($criteria, $context)->getEntities();

        return $result
            ->filter(fn(SystemConfigEntity $entity) => $entity->getSalesChannelId())
            ->map(fn(SystemConfigEntity $entity) => new ErgonodeAccessData(
                $baseUrl,
                strval($entity->getConfigurationValue()),
                $entity->getSalesChannelId()
            ));
    }

    public function getErgonodeAccessData(?string $salesChannelId = null): ErgonodeAccessData
    {
        return new ErgonodeAccessData(
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeBaseUrl', $salesChannelId),
            $this->configService->getString(self::STRIX_ERGONODE_CONFIG_NAMESPACE . 'ergonodeApiKey', $salesChannelId),
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
}
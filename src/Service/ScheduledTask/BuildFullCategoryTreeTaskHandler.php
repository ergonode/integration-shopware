<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Persistor\CategoryPersistor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeCategoryProvider;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class BuildFullCategoryTreeTaskHandler extends AbstractSyncTaskHandler
{
    private ConfigProvider $configProvider;

    private ErgonodeCategoryProvider $categoryProvider;

    private CategoryPersistor $categoryPersistor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryLogger,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        ConfigProvider $configProvider,
        ErgonodeCategoryProvider $categoryProvider,
        CategoryPersistor $categoryPersistor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryLogger, $lockFactory, $ergonodeSyncLogger);

        $this->configProvider = $configProvider;
        $this->categoryProvider = $categoryProvider;
        $this->categoryPersistor = $categoryPersistor;
    }

    public static function getHandledMessages(): iterable
    {
        return [BuildFullCategoryTreeTask::class];
    }

    public function runSync(): int
    {
        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            $this->logger->error('Could not find category tree code in plugin config.');

            return 0;
        }

        try {
            $categoryCollection = $this->categoryProvider->provideCategoryTree($categoryTreeCode);

            if (empty($categoryCollection)) {
                $this->logger->error('Ergonode request failed.');

                return 0;
            }

            $this->categoryPersistor->persistCollection($categoryCollection, $this->context);

            $this->logger->info('Processed category tree.', [
                'categoryCount' => $categoryCollection->count(),
            ]);

            return $categoryCollection->count();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return 0;
    }
}
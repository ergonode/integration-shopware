<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\CategorySyncProcessor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class CategorySyncTaskHandler extends AbstractSyncTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private CategorySyncProcessor $categorySyncProcessor;

    private ConfigProvider $configProvider;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        CategorySyncProcessor $categorySyncProcessor,
        ConfigProvider $configProvider
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->categorySyncProcessor = $categorySyncProcessor;
        $this->configProvider = $configProvider;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategorySyncTask::class];
    }

    public function runSync(): int
    {
        $currentPage = 0;
        $count = 0;

        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            $this->logger->error('Could not find category tree code in plugin config.');

            return 0;
        }

        try {
            do {
                $result = $this->categorySyncProcessor->processStream($categoryTreeCode, $this->context);

                if (null === $result) {
                    break;
                }

                $count += $result->getProcessedEntityCount();

                if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                    break;
                }
            } while ($result->hasNextPage());
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}
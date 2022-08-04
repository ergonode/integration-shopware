<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\CategoryTreeSyncProcessor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class CategoryTreeSyncTaskHandler extends AbstractSyncTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private ConfigProvider $configProvider;

    private CategoryTreeSyncProcessor $categoryTreeSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $ergonodeSyncLogger,
        LockFactory $lockFactory,
        ConfigProvider $configProvider,
        CategoryTreeSyncProcessor $categoryTreeSyncProcessor
    ) {
        parent::__construct($scheduledTaskRepository, $syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->configProvider = $configProvider;
        $this->categoryTreeSyncProcessor = $categoryTreeSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategoryTreeSyncTask::class];
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
                $result = $this->categoryTreeSyncProcessor->processStream($this->context);

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
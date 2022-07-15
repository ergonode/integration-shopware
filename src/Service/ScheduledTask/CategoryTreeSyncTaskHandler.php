<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\CategoryTreeSyncProcessor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Lock\LockFactory;

class CategoryTreeSyncTaskHandler extends ScheduledTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private ConfigProvider $configProvider;
    private LoggerInterface $logger;
    private LockFactory $lockFactory;
    private CategoryTreeSyncProcessor $categoryTreeSyncProcessor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        CategoryTreeSyncProcessor $categoryTreeSyncProcessor,
        ConfigProvider $configProvider,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->configProvider = $configProvider;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
        $this->categoryTreeSyncProcessor = $categoryTreeSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [CategoryTreeSyncTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.category-tree-sync-lock');

        if (false === $lock->acquire()) {
            $this->logger->info('CategoryTreeSyncTask is locked');

            return;
        }

        $this->logger->info('Starting CategoryTreeSyncTask...');

        $context = new Context(new SystemSource());
        $currentPage = 0;

        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            $this->logger->error('Could not find category tree code in plugin config.');

            return;
        }

        try {
            while ($this->categoryTreeSyncProcessor->processStream($context)) {
                if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
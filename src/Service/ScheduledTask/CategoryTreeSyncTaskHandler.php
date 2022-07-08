<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Persistor\CategoryPersistor;
use Strix\Ergonode\Provider\ConfigProvider;
use Strix\Ergonode\Provider\ErgonodeCategoryProvider;
use Symfony\Component\Lock\LockFactory;

class CategoryTreeSyncTaskHandler extends ScheduledTaskHandler
{
    private ConfigProvider $configProvider;
    private LoggerInterface $logger;
    private LockFactory $lockFactory;
    private ErgonodeCategoryProvider $categoryProvider;
    private CategoryPersistor $categoryPersistor;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ConfigProvider $configProvider,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory,
        ErgonodeCategoryProvider $categoryProvider,
        CategoryPersistor $categoryPersistor
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->configProvider = $configProvider;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
        $this->categoryProvider = $categoryProvider;
        $this->categoryPersistor = $categoryPersistor;
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

        $categoryTreeCode = $this->configProvider->getCategoryTreeCode();
        if (empty($categoryTreeCode)) {
            $this->logger->error('Could not find category tree code in plugin config.');

            return;
        }

        try {
            $categoryCollection = $this->categoryProvider->provideCategoryTree($categoryTreeCode);

            if (empty($categoryCollection)) {
                $this->logger->error('Request failed');

                return;
            }

            $this->categoryPersistor->persistCollection($categoryCollection, $context);

            $this->logger->info('Processed category tree',
                [
                    'categoryCount' => $categoryCollection->count()
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
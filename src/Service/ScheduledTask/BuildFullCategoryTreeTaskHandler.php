<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Persistor\CategoryPersistor;
use Ergonode\IntegrationShopware\Provider\ConfigProvider;
use Ergonode\IntegrationShopware\Provider\ErgonodeCategoryProvider;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Lock\LockFactory;

class BuildFullCategoryTreeTaskHandler extends ScheduledTaskHandler
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
        return [BuildFullCategoryTreeTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.build-full-category-tree-lock');

        if (false === $lock->acquire()) {
            $this->logger->info('BuildFullCategoryTreeTaskHandler is locked');

            return;
        }

        $this->logger->info('Starting BuildFullCategoryTreeTaskHandler...');

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
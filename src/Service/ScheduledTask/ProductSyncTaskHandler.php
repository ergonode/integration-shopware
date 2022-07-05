<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Processor\ProductSyncProcessor;
use Symfony\Component\Lock\LockFactory;

class ProductSyncTaskHandler extends ScheduledTaskHandler
{
    private const MAX_PAGES_PER_RUN = 25;

    private ProductSyncProcessor $productSyncProcessor;
    private LoggerInterface $logger;
    private LockFactory $lockFactory;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ProductSyncProcessor $productSyncProcessor,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->productSyncProcessor = $productSyncProcessor;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
    }

    public static function getHandledMessages(): iterable
    {
        return [ProductSyncTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.product-sync-lock');

        if (false === $lock->acquire()) {
            $this->logger->info('ProductSyncTask is locked');

            return;
        }

        $this->logger->info('Starting ProductSyncTask...');

        $context = new Context(new SystemSource());
        $currentPage = 0;

        while ($this->productSyncProcessor->processStream($context)) {
            if ($currentPage++ >= self::MAX_PAGES_PER_RUN) {
                break;
            }
        }
    }
}
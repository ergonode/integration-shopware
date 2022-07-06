<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Processor\ProductVisibilitySyncProcessor;
use Symfony\Component\Lock\LockFactory;

class ProductVisibilitySyncTaskHandler extends ScheduledTaskHandler
{
    private Context $context;

    private ProductVisibilitySyncProcessor $productVisibilitySyncProcessor;

    private LoggerInterface $logger;

    private LockFactory $lockFactory;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ProductVisibilitySyncProcessor $productVisibilitySyncProcessor,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->context = new Context(new SystemSource());
        $this->productVisibilitySyncProcessor = $productVisibilitySyncProcessor;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
    }

    public static function getHandledMessages(): iterable
    {
        return [ProductVisibilitySyncTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.product-visibility-sync-lock');

        if (!$lock->acquire()) {
            $this->logger->info('ProductVisibilitySyncTask is locked');

            return;
        }

        $this->productVisibilitySyncProcessor->processStream($this->context);
    }
}
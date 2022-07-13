<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\AttributeSyncProcessor;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class AttributeSyncTaskHandler extends ScheduledTaskHandler
{
    private Context $context;

    private AttributeSyncProcessor $attributeSyncProcessor;

    private LoggerInterface $logger;

    private LockFactory $lockFactory;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        AttributeSyncProcessor $attributeSyncProcessor,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->context = new Context(new SystemSource());
        $this->attributeSyncProcessor = $attributeSyncProcessor;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
    }

    public static function getHandledMessages(): iterable
    {
        return [AttributeSyncTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.attribute-sync-lock');

        if (!$lock->acquire()) {
            $this->logger->info('AttributeSyncTask is locked');

            return;
        }

        try {
            $this->attributeSyncProcessor->process($this->context);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
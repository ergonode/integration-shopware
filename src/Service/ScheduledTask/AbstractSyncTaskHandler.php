<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Lock\LockFactory;

use function sprintf;

abstract class AbstractSyncTaskHandler extends ScheduledTaskHandler
{
    protected Context $context;

    protected SyncHistoryLogger $syncHistoryService;

    protected LockFactory $lockFactory;

    protected LoggerInterface $logger;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->context = new Context(new SystemSource());
        $this->syncHistoryService = $syncHistoryService;
        $this->lockFactory = $lockFactory;
        $this->logger = $ergonodeSyncLogger;
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock($this->getLockName());
        if (false === $lock->acquire()) {
            $this->logger->error(sprintf('%s is locked.', $this->getTaskName()));

            return;
        }

        $id = $this->syncHistoryService->start($this->getTaskName(), $this->context);

        $count = $this->runSync();

        $this->syncHistoryService->finish($id, $this->context, $count);
    }

    protected function getTaskName(): string
    {
        $ref = new ReflectionClass($this);

        return str_replace('TaskHandler', '', $ref->getShortName());
    }

    protected function getLockName(): string
    {
        return sprintf('%s.%s.%s', 'ErgonodeIntegration', $this->getTaskName(), 'lock');
    }

    abstract protected function runSync(): int;
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
//use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function sprintf;

//TODO SWER-14
#[AsMessageHandler]
abstract class AbstractSyncHandler
{
    protected Context $context;

    protected SyncHistoryLogger $syncHistoryService;

    protected LockFactory $lockFactory;

    protected LoggerInterface $logger;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger
    ) {
        $this->context = $this->createContext();
        $this->syncHistoryService = $syncHistoryService;
        $this->lockFactory = $lockFactory;
        $this->logger = $ergonodeSyncLogger;
    }

    public function handle($message): void
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

        return str_replace('Handler', '', $ref->getShortName());
    }

    protected function getLockName(): string
    {
        return sprintf('%s.%s.%s', 'ErgonodeIntegration', $this->getTaskName(), 'lock');
    }

    protected function createContext(): Context
    {
        return new Context(new SystemSource());
    }

    abstract protected function runSync(): int;
}

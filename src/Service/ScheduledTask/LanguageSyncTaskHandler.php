<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\ScheduledTask;

use Ergonode\IntegrationShopware\Processor\LanguageSyncProcessor;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class LanguageSyncTaskHandler extends ScheduledTaskHandler
{
    private Context $context;

    private LanguageSyncProcessor $languageSyncProcessor;

    private LoggerInterface $logger;

    private LockFactory $lockFactory;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        LanguageSyncProcessor $languageSyncProcessor,
        LoggerInterface $syncLogger,
        LockFactory $lockFactory
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->context = new Context(new SystemSource());
        $this->languageSyncProcessor = $languageSyncProcessor;
        $this->logger = $syncLogger;
        $this->lockFactory = $lockFactory;
    }

    public static function getHandledMessages(): iterable
    {
        return [LanguageSyncTask::class];
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('strix.ergonode.language-sync-lock');

        if (!$lock->acquire()) {
            $this->logger->info('LanguageSyncTask is locked');

            return;
        }

        try {
            $this->languageSyncProcessor->process($this->context);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
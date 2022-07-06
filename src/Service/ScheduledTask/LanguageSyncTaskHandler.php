<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Strix\Ergonode\Processor\LanguageSyncProcessor;
use Symfony\Component\Lock\LockFactory;

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

        $this->languageSyncProcessor->process($this->context);
    }
}
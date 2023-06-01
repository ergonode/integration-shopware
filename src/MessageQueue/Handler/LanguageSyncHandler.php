<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\LanguageSync;
use Ergonode\IntegrationShopware\Processor\LanguageSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class LanguageSyncHandler extends AbstractSyncHandler
{
    private LanguageSyncProcessor $languageSyncProcessor;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        LanguageSyncProcessor $languageSyncProcessor
    ) {
        parent::__construct($syncHistoryService, $lockFactory, $ergonodeSyncLogger);

        $this->languageSyncProcessor = $languageSyncProcessor;
    }

    public function __invoke(LanguageSync $message)
    {
        $this->handleMessage($message);
    }

    public function runSync($message): int
    {
        $count = 0;

        try {
            $result = $this->languageSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}

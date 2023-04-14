<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\DeletedAttributeSync;
use Ergonode\IntegrationShopware\Processor\DeletedAttributesSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class DeletedAttributeSyncHandler extends AbstractSyncHandler
{
    private DeletedAttributesSyncProcessor $deletedAttributesSyncProcessor;

    public function __construct(
        SyncHistoryLogger $syncHistoryLogger,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        DeletedAttributesSyncProcessor $deletedAttributesSyncProcessor
    ) {
        parent::__construct($syncHistoryLogger, $lockFactory, $ergonodeSyncLogger);

        $this->deletedAttributesSyncProcessor = $deletedAttributesSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [DeletedAttributeSync::class];
    }

    public function runSync($message): int
    {
        $count = 0;

        try {
            $result = $this->deletedAttributesSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}

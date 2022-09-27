<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\MessageQueue\Handler;

use Ergonode\IntegrationShopware\MessageQueue\Message\AttributeSync;
use Ergonode\IntegrationShopware\Processor\AttributeSyncProcessor;
use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Throwable;

class AttributeSyncHandler extends AbstractSyncHandler
{
    private AttributeSyncProcessor $attributeSyncProcessor;

    public function __construct(
        SyncHistoryLogger $syncHistoryLogger,
        LockFactory $lockFactory,
        LoggerInterface $ergonodeSyncLogger,
        AttributeSyncProcessor $attributeSyncProcessor
    ) {
        parent::__construct($syncHistoryLogger, $lockFactory, $ergonodeSyncLogger);

        $this->attributeSyncProcessor = $attributeSyncProcessor;
    }

    public static function getHandledMessages(): iterable
    {
        return [AttributeSync::class];
    }

    public function runSync(): int
    {
        $count = 0;

        try {
            $result = $this->attributeSyncProcessor->process($this->context);
            $count = $result->getProcessedEntityCount();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $count;
    }
}

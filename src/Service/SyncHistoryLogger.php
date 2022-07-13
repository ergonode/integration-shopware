<?php

declare(strict_types=1);

namespace Strix\Ergonode\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;

class SyncHistoryLogger
{
    private ?string $syncHistoryId = null;

    private LoggerInterface $syncLogger;

    private EntityRepositoryInterface $repository;

    public function __construct(
        LoggerInterface $syncLogger,
        EntityRepositoryInterface $repository
    ) {
        $this->syncLogger = $syncLogger;
        $this->repository = $repository;
    }

    public function start(string $name): void
    {
        $this->initLogger($name);

        $this->syncLogger->info(
            sprintf('Started %s synchronization.', $name)
        );
    }

    public function step(): void
    {
        $this->syncLogger->info('');
    }

    public function finish(string $id, int $totalSuccess, int $totalError): void
    {

    }

    private function initLogger(string $name): void
    {
        // todo create new sync history entry
        $id = Uuid::randomHex();
        $this->syncHistoryId = $id;

        // todo set logger file with $id
        $this->syncLogger->
    }
}
<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\History;

use DateTime;
use Ergonode\IntegrationShopware\Entity\ErgonodeSyncHistory\ErgonodeSyncHistoryDefinition;
use Ergonode\IntegrationShopware\Entity\ErgonodeSyncHistory\ErgonodeSyncHistoryEntity;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Throwable;

use function array_filter;
use function count;
use function json_decode;
use function rtrim;
use function sprintf;
use function str_replace;

class SyncHistoryLogger
{
    private const LOG_DIR_NAME = 'ergonode_integration/history';

    private string $kernelLogsDir;

    private string $kernelEnv;

    private LoggerInterface $syncLogger;

    private EntityRepositoryInterface $repository;

    public function __construct(
        string $kernelLogsDir,
        string $kernelEnv,
        LoggerInterface $syncLogger,
        EntityRepositoryInterface $repository
    ) {
        $this->kernelLogsDir = $kernelLogsDir;
        $this->kernelEnv = $kernelEnv;
        $this->syncLogger = $syncLogger;
        $this->repository = $repository;
    }

    public function start(string $name, Context $context): string
    {
        $syncHistoryId = $this->initLogger($name, $context);

        $this->syncLogger->info(
            sprintf('Started %s.', $name)
        );

        return $syncHistoryId;
    }

    public function finish(string $id, Context $context, ?int $totalSuccess = null): void
    {
        $entity = $this->getSyncHistoryEntity($id, $context);
        if (null === $entity) {
            return;
        }

        $totalError = $this->countLogErrors($id);

        $payload = [
            'id' => $id,
            'status' => ErgonodeSyncHistoryEntity::STATUS_FINISHED,
            'totalError' => $totalError,
            'endDate' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        if (null !== $totalSuccess) {
            $payload['totalSuccess'] = $totalSuccess;
        }

        $this->repository->update([$payload], $context);

        $this->syncLogger->info(
            sprintf('Finished %s.', $entity->getName()),
            [
                'totalSuccess' => null !== $totalSuccess ? $totalSuccess : $entity->getTotalSuccess(),
                'totalError' => $totalError,
            ]
        );
    }

    public function getLogs(string $id): array
    {
        $path = $this->buildLogFilename($id);

        try {
            $content = file_get_contents($path);
        } catch (Throwable $e) {
            // let $content be unset; validating below
        }

        if (empty($content)) {
            return [];
        }

        // parse to proper JSON string
        $content = rtrim($content, "\n");
        $content = str_replace("\n", ',', $content);
        $content = sprintf('[%s]', $content);

        return json_decode($content, true) ?: [];
    }

    private function countLogErrors(string $id): int
    {
        $logs = $this->getLogs($id);

        $errors = array_filter($logs, fn(array $log) => $log['level'] >= Logger::ERROR);

        return count($errors);
    }

    private function buildLogFilename(string $syncHistoryId): string
    {
        return sprintf(
            '%s/%s/%s.%s.log',
            $this->kernelLogsDir,
            self::LOG_DIR_NAME,
            $syncHistoryId,
            $this->kernelEnv
        );
    }

    private function initLogger(string $name, Context $context): string
    {
        $syncHistoryId = $this->createSyncHistoryEntity($name, $context);

        // configure logger for this specific sync
        if ($this->syncLogger instanceof Logger) {
            $poppedHandled = $this->syncLogger->popHandler();
            if (!$poppedHandled instanceof SyncHistoryHandler) {
                // unshift handler back to the stack
                $this->syncLogger->pushHandler($poppedHandled);
            }

            $handler = new SyncHistoryHandler($this->buildLogFilename($syncHistoryId));
            $handler->setFormatter(new JsonFormatter());
            $this->syncLogger->pushHandler($handler);
            $this->syncLogger->pushProcessor(new SyncHistoryProcessor($syncHistoryId));
        }

        return $syncHistoryId;
    }

    private function getSyncHistoryEntity(string $id, Context $context): ?ErgonodeSyncHistoryEntity
    {
        $criteria = new Criteria([$id]);

        /** @var ErgonodeSyncHistoryEntity|null $result */
        $result = $this->repository->search($criteria, $context)->first();

        return $result;
    }

    private function createSyncHistoryEntity(string $name, Context $context): string
    {
        $written = $this->repository->create([
            [
                'name' => $name,
                'status' => ErgonodeSyncHistoryEntity::STATUS_STARTED,
                'totalSuccess' => 0,
                'totalError' => 0,
                'startDate' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
        ], $context);

        $id = $written->getPrimaryKeys(ErgonodeSyncHistoryDefinition::ENTITY_NAME)[0] ?? null;
        if (null === $id) {
            throw new RuntimeException('Could not persist sync history entity.');
        }

        return $id;
    }
}
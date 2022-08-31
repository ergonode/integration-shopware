<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\OrphanEntitiesManager;
use Ergonode\IntegrationShopware\Util\SyncPerformanceLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;

use function array_reduce;
use function count;

class DeletedAttributesSyncProcessor
{
    private LoggerInterface $logger;

    private OrphanEntitiesManager $orphanEntitiesManager;

    private SyncPerformanceLogger $performanceLogger;

    public function __construct(
        LoggerInterface $ergonodeSyncLogger,
        OrphanEntitiesManager $orphanEntitiesManager,
        SyncPerformanceLogger $performanceLogger
    ) {
        $this->logger = $ergonodeSyncLogger;
        $this->orphanEntitiesManager = $orphanEntitiesManager;
        $this->performanceLogger = $performanceLogger;
    }

    public function process(Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $stopwatch = new Stopwatch();
        $processedEntityCount = 0;

        try {
            $stopwatch->start('process');
            $result = $this->orphanEntitiesManager->cleanAttributes($context);
            $stopwatch->stop('process');

            if (false === empty($result)) {
                $processedEntityCount = array_reduce($result, static fn($carry, $item) => $carry + count($item));
            }

            $this->logger->info('Processed deleted attributes', [
                'processedCount' => $processedEntityCount,
                'result' => $result
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Error occurred while deleting attributes from stream',
                [
                    'exception' => $e
                ]
            );
        }

        $counter->setHasNextPage(false);
        $counter->incrProcessedEntityCount($processedEntityCount ?? 0);
        $counter->setStopwatch($stopwatch);
        $this->performanceLogger->logPerformance(self::class, $stopwatch);

        return $counter;
    }
}
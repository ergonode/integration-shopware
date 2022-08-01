<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Processor;

use Ergonode\IntegrationShopware\DTO\SyncCounterDTO;
use Ergonode\IntegrationShopware\Manager\OrphanEntitiesManager;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class DeletedAttributesSyncProcessor
{
    private LoggerInterface $logger;

    private OrphanEntitiesManager $orphanEntitiesManager;

    public function __construct(
        LoggerInterface $syncLogger,
        OrphanEntitiesManager $orphanEntitiesManager
    ) {
        $this->logger = $syncLogger;
        $this->orphanEntitiesManager = $orphanEntitiesManager;
    }

    public function process(Context $context): SyncCounterDTO
    {
        $counter = new SyncCounterDTO();
        $processedEntityCount = 0;

        try {
            $result = $this->orphanEntitiesManager->cleanAttributes($context);

            if (false === empty($result)) {
                $processedEntityCount = \array_reduce($result, static fn($carry, $item) => $carry + \count($item));
            }

            $this->logger->info('Processed deleted attributes', [
                'processedCount' => $processedEntityCount,
                'result' => $result
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Error occurred while deleting attributes from stream',
                [
                    'exception' => $e
                ]
            );
        }

        $counter->setHasNextPage(false);
        $counter->incrProcessedEntityCount($processedEntityCount ?? 0);

        return $counter;
    }
}
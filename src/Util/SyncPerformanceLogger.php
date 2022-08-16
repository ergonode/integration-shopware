<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class SyncPerformanceLogger
{
    private LoggerInterface $logger;
    private bool $enabled;

    public function __construct(LoggerInterface $ergonodeSyncLogger, bool $enabled)
    {
        $this->logger = $ergonodeSyncLogger;
        $this->enabled = $enabled;
    }

    public function logPerformance(string $name, Stopwatch $stopwatch): void
    {
        if (false === $this->enabled) {
            return;
        }

        $performanceInfo = [];
        foreach ($stopwatch->getSections()['__root__']->getEvents() as $event) {
            $performanceInfo[$event->getName() . '_time'] = \sprintf('%.02fms', $event->getDuration());
            $performanceInfo[$event->getName() . '_memory'] = \sprintf('%.02fMB', $event->getMemory() / 1024 / 1024);
        }

        $performanceInfo['name'] = $name;
        $this->logger->info('Performance report', $performanceInfo);
    }
}
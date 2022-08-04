<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class SyncPerformanceLogger
{
    private LoggerInterface $syncLogger;

    public function __construct(LoggerInterface $syncLogger)
    {
        $this->syncLogger = $syncLogger;
    }

    public function logPerformance(string $name, Stopwatch $stopwatch): void
    {
        $performanceInfo = [];
        foreach ($stopwatch->getSections()['__root__']->getEvents() as $event) {
            $performanceInfo[$event->getName() . '_time'] = \sprintf('%.02fms', $event->getDuration());
            $performanceInfo[$event->getName() . '_memory'] = \sprintf('%.02fMB', $event->getMemory() / 1024 / 1024);
        }

        $performanceInfo['name'] = $name;
        $this->syncLogger->info('Performance report', $performanceInfo);
    }
}
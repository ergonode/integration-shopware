<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

use Symfony\Component\Stopwatch\Stopwatch;

class SyncCounterDTO
{
    private bool $hasNextPage = false;

    private int $processedEntityCount = 0;

    private array $primaryKeys = [];

    private ?Stopwatch $stopwatch = null;

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function setHasNextPage(bool $hasNextPage): void
    {
        $this->hasNextPage = $hasNextPage;
    }

    public function getProcessedEntityCount(): int
    {
        return $this->processedEntityCount;
    }

    public function incrProcessedEntityCount(int $incrCount = 1): void
    {
        $this->processedEntityCount += $incrCount;
    }

    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    public function setPrimaryKeys(array $primaryKeys): void
    {
        $this->primaryKeys = $primaryKeys;
    }

    public function getStopwatch(): ?Stopwatch
    {
        return $this->stopwatch;
    }

    public function setStopwatch(Stopwatch $stopwatch): void
    {
        $this->stopwatch = $stopwatch;
    }

    public function hasStopwatch(): bool
    {
        return null !== $this->stopwatch;
    }
}
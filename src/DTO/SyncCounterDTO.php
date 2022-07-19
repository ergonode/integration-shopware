<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\DTO;

class SyncCounterDTO
{
    private bool $hasNextPage = false;

    private int $processedEntityCount = 0;

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
}
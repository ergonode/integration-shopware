<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\History;

class SyncHistoryProcessor
{
    private ?string $syncHistoryId;

    public function __construct(?string $syncHistoryId = null)
    {
        $this->syncHistoryId = $syncHistoryId;
    }

    public function __invoke(array $record): array
    {
        if (null === $this->syncHistoryId) {
            return $record;
        }

        $record['context']['syncHistoryId'] = $this->syncHistoryId;

        return $record;
    }
}
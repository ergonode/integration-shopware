<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\History;

use Monolog\LogRecord;

class SyncHistoryProcessor
{
    private ?string $syncHistoryId;

    public function __construct(?string $syncHistoryId = null)
    {
        $this->syncHistoryId = $syncHistoryId;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (null === $this->syncHistoryId) {
            return $record;
        }

        $extra = $record->extra;
        $extra['syncHistoryId'] = $this->syncHistoryId;
        $record->extra = $extra;

        return $record;
    }
}

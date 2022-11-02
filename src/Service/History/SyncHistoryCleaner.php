<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service\History;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Ergonode\IntegrationShopware\Entity\ErgonodeSyncHistory\ErgonodeSyncHistoryDefinition;

class SyncHistoryCleaner
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function clean(int $interval): void
    {
        $this->connection->executeStatement(sprintf(
            'DELETE FROM %s WHERE `created_at` < NOW() - %u',
            ErgonodeSyncHistoryDefinition::ENTITY_NAME,
            $interval
        ));
    }
}
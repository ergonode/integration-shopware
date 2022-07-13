<?php declare(strict_types=1);

namespace Strix\Ergonode\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1657708708CreateErgonodeSyncHistoryTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1657708708;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `strix_ergonode_sync_history` (
              `id` BINARY(16) NOT NULL,
              `name` VARCHAR(128) NOT NULL,
              `status` VARCHAR(128) NOT NULL,
              `total_success` INTEGER NOT NULL,
              `total_error` INTEGER NOT NULL,
              `start_date` DATETIME(3) NOT NULL,
              `end_date` DATETIME(3) NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

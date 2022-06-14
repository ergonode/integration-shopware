<?php declare(strict_types=1);

namespace Strix\Ergonode\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1655286294CreateErgonodeCursorTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1655286294;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `strix_ergonode_cursor` (
              `id` BINARY(16) NOT NULL,
              `cursor` VARCHAR(128) NOT NULL,
              `query` VARCHAR(128) NOT NULL UNIQUE,
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

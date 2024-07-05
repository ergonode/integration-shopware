<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1701699813CreateErgonodeCategoryAttributeMappingTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1701699813;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `ergonode_category_attribute_mapping` (
              `id` BINARY(16) NOT NULL,
              `shopware_key` VARCHAR(128) NOT NULL UNIQUE,
              `ergonode_key` VARCHAR(128) NOT NULL,
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

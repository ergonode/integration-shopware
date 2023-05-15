<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1683794806CreateErgonodeAttributeMappingTable extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1683794806;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `ergonode_category_mapping` (
              `id` BINARY(16) NOT NULL,
              `shopware_id` BINARY(16) NOT NULL UNIQUE,
              `ergonode_key` VARCHAR(128) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');


        $this->updateInheritance($connection, CategoryDefinition::ENTITY_NAME, 'shopwareId');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

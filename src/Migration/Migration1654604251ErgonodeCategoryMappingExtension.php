<?php

declare(strict_types=1);

namespace Strix\Ergonode\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;
use Strix\Ergonode\Extension\ErgonodeCategoryMappingExtension;

class Migration1654604251ErgonodeCategoryMappingExtension extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1654604251;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `ergonode_category_mapping_extension` (
              `id` BINARY(16) NOT NULL,
              `code` VARCHAR(128) NOT NULL,
              `locale` VARCHAR(5) NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $this->updateInheritance($connection, CategoryDefinition::ENTITY_NAME, ErgonodeCategoryMappingExtension::STORAGE_NAME);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

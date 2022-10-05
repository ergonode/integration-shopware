<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1664962095AlterCategoryLastChildMappingNullableCategoryId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1664962095;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `category_last_child_mapping` 
                modify category_id binary(16) null;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1665058546CategoryMappingExtensionTreeCode extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1665058546;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE ergonode_category_mapping_extension
                ADD tree_code varchar(128) not null;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

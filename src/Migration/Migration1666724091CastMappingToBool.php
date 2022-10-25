<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1666724091CastMappingToBool extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1666724091;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ergonode_attribute_mapping`
            ADD COLUMN `cast_to_bool` TINYINT(1) DEFAULT 0
        ');

        $connection->executeStatement('
            ALTER TABLE `ergonode_custom_field_mapping`
            ADD COLUMN `cast_to_bool` TINYINT(1) DEFAULT 0
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

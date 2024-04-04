<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1712230086ChaneCollationForUniqueMappings extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1712230086;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            ALTER TABLE `ergonode_mapping_extension`
                CHANGE COLUMN `code` `code` VARCHAR(128) NOT NULL COLLATE 'utf8_bin' AFTER `id`;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

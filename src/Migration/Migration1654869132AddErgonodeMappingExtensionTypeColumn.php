<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1654869132AddErgonodeMappingExtensionTypeColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1654869132;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `strix_ergonode_mapping_extension`
            ADD COLUMN `type` VARCHAR(128) NOT NULL AFTER `code`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

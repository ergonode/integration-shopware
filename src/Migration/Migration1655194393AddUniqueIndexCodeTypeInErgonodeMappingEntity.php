<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1655194393AddUniqueIndexCodeTypeInErgonodeMappingEntity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1655194393;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            ALTER TABLE `strix_ergonode_mapping_extension`
            ADD UNIQUE (`code`, `type`);
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

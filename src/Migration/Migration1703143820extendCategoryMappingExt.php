<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1703143820extendCategoryMappingExt extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1703143820;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ergonode_category_mapping_extension` ADD `active` tinyint(1) NOT NULL DEFAULT 0 AFTER `code`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

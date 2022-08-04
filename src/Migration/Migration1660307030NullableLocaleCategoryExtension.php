<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1660307030NullableLocaleCategoryExtension extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1660307030;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ergonode_category_mapping_extension`
            modify locale varchar(5) null;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\System\Unit\UnitDefinition;

class Migration1675410272UnitErgonodeMappingExtension extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1675410272;
    }

    public function update(Connection $connection): void
    {
        $this->updateInheritance($connection, UnitDefinition::ENTITY_NAME, 'ergonode_mapping_extension_id');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

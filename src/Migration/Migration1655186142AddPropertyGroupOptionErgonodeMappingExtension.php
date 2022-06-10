<?php
declare(strict_types=1);

namespace Strix\Ergonode\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1655186142AddPropertyGroupOptionErgonodeMappingExtension extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1655186142;
    }

    public function update(Connection $connection): void
    {
        $this->updateInheritance($connection, PropertyGroupOptionDefinition::ENTITY_NAME, 'ergonode_mapping_extension_id');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1654756949AddPropertyGroupErgonodeMappingExtension extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1654756949;
    }

    public function update(Connection $connection): void
    {
        $this->updateInheritance($connection, PropertyGroupDefinition::ENTITY_NAME, 'ergonode_mapping_extension_id');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

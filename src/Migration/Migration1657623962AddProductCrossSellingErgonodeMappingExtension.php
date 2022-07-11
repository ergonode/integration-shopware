<?php
declare(strict_types=1);

namespace Strix\Ergonode\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1657623962AddProductCrossSellingErgonodeMappingExtension extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1657623962;
    }

    public function update(Connection $connection): void
    {
        $this->updateInheritance($connection, ProductCrossSellingDefinition::ENTITY_NAME, 'ergonode_mapping_extension_id');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

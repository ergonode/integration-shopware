<?php declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Migration;

use Doctrine\DBAL\Connection;
use Ergonode\IntegrationShopware\Api\CategoryStreamResultsProxy;
use Ergonode\IntegrationShopware\Api\CategoryTreeStreamResultsProxy;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Command deletes existing category cursors. Cursors should be only used for pagination.
 */
class Migration1690444403DeleteLegacyCursors extends MigrationStep
{

    public function getCreationTimestamp(): int
    {
        return 1690444403;
    }

    public function update(Connection $connection): void
    {
        $result = $connection->fetchOne('SHOW TABLES LIKE \'ergonode_cursor\';');

        if ($result !== false) {
            $connection->executeStatement(
                'DELETE FROM `ergonode_cursor` where query in (:types)',
                [
                    'types' => [
                        CategoryStreamResultsProxy::MAIN_FIELD,
                        CategoryTreeStreamResultsProxy::MAIN_FIELD,
                        CategoryTreeStreamResultsProxy::TREE_LEAF_LIST_CURSOR,
                    ],
                ],
                [
                    'types' => Connection::PARAM_STR_ARRAY,
                ]
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

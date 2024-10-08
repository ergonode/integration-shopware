<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Lifecycle;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use function sprintf;

class DatabaseLifecycleManager
{
    private static ?self $instance = null;

    private static array $tableNames = [
        'ergonode_attribute_mapping',
        'ergonode_category_attribute_mapping',
        'ergonode_category_mapping_extension',
        'ergonode_mapping_extension',
        'ergonode_cursor',
        'ergonode_sync_history',
        'ergonode_category_mapping',
        'ergonode_custom_field_mapping',
    ];

    private static array $extendedTableNames = [
        'category' => [
            'ergonode_category_mapping_extension_id',
            'shopwareId'
        ],
        'product_cross_selling' => 'ergonode_mapping_extension_id',
        'property_group' => 'ergonode_mapping_extension_id',
        'property_group_option' => 'ergonode_mapping_extension_id',
        'delivery_time' => 'ergonode_mapping_extension_id',
    ];

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getInstance(ContainerInterface $container): self
    {
        if (null === self::$instance) {
            self::$instance = new self(
                $container->get(Connection::class)
            );
        }

        return self::$instance;
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->dropTables();
        $this->dropExtensionFields();
    }

    private function dropTables(): void
    {
        foreach (self::$tableNames as $tableName) {
            $this->connection->executeStatement(
                sprintf('DROP TABLE IF EXISTS `%s`', $tableName)
            );
        }
    }

    private function dropExtensionFields(): void
    {
        foreach (self::$extendedTableNames as $tableName => $fieldNames) {
            if (!is_array($fieldNames)) {
                $fieldNames = [$fieldNames];
            }
            foreach ($fieldNames as $fieldName) {
                $result = $this->connection->fetchOne(
                    sprintf('SHOW COLUMNS FROM `%s` WHERE `field` LIKE \'%s\'', $tableName, $fieldName)
                );
                if ($result !== false) {
                    $this->connection->executeStatement(
                        sprintf('ALTER TABLE `%s` DROP COLUMN %s', $tableName, $fieldName)
                    );
                }
            }
        }
    }
}

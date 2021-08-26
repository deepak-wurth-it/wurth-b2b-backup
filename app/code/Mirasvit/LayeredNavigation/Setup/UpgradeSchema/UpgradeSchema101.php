<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $oldTableName = $setup->getTable('mst_ln_attribute_settings');

        if (!$connection->isTableExists($oldTableName)) {
            return;
        }

        $connection->delete($oldTableName);

        $tableName = $setup->getTable('mst_navigation_attribute_config');

        $connection->dropForeignKey($oldTableName, $setup->getFkName(
            'mst_ln_attribute_settings',
            'mst_attribute_id',
            'eav_attribute',
            'attribute_id'
        ));

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }

        $connection->renameTable($oldTableName, $tableName);

        $connection->changeColumn($tableName, 'mst_settings_id', 'config_id', [
            'type'     => Table::TYPE_INTEGER,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
            'length'   => null,
        ]);

        $connection->changeColumn($tableName, 'mst_attribute_id', 'attribute_id', [
            'type'   => Table::TYPE_INTEGER,
            'length' => null,
        ]);

        $connection->changeColumn($tableName, 'mst_attribute_code', 'attribute_code', [
            'type'   => Table::TYPE_TEXT,
            'length' => 255,
        ]);

        $connection->addColumn($tableName, 'config', [
            'type'    => Table::TYPE_TEXT,
            'length'  => 65500,
            'comment' => 'Config',
        ]);

        $connection->dropColumn($tableName, 'mst_is_slider');
        $connection->dropColumn($tableName, 'mst_image_options');
        $connection->dropColumn($tableName, 'mst_filter_text');
        $connection->dropColumn($tableName, 'mst_is_whole_width_image');
    }
}

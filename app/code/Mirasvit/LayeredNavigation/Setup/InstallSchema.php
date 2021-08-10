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

namespace Mirasvit\LayeredNavigation\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Attribute setting table
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_ln_attribute_settings')
        )->addColumn(
            'mst_settings_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'mst_attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id'
        )->addColumn(
            'mst_is_slider',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => 0],
            'Is slider'
        )->addColumn(
            'mst_attribute_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Attribute code'
        )->addColumn(
            'mst_image_options',
            Table::TYPE_TEXT,
            1024,
            ['nullable' => true],
            'Image'
        )->addColumn(
            'mst_filter_text',
            Table::TYPE_TEXT,
            1024,
            ['nullable' => true],
            'Menu text'
        )->addColumn(
            'mst_is_whole_width_image',
            Table::TYPE_TEXT,
            1024,
            ['nullable' => true],
            'Whole width picture'
        )->addForeignKey(
            $installer->getFkName(
                'mst_ln_attribute_settings',
                'mst_attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'mst_attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Attribute setting table'
        );

        $installer->getConnection()->dropTable($installer->getTable('mst_ln_attribute_settings'));
        $installer->getConnection()->createTable($table);

        //Mirasvit Layered Navigation Rating
        $installer->getConnection()->addIndex(
            $installer->getTable('review_entity_summary'),
            'mirasvit_layered_navigation_rating',
            ['entity_type', 'entity_pk_value', 'store_id']
        );
    }
}

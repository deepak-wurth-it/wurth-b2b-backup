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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\SearchLanding\Api\Data\PageInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'mst_search_landing_page'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_search_landing_page')
        )->addColumn(
            PageInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Page Id'
        )->addColumn(
            PageInterface::QUERY_TEXT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Query Text'
        )->addColumn(
            PageInterface::URL_KEY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Url Key'
        )->addColumn(
            PageInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            PageInterface::META_DESCRIPTION,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            PageInterface::META_KEYWORDS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Title'
        )->addColumn(
            PageInterface::LAYOUT_UPDATE,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Layout Update'
        )->addColumn(
            PageInterface::STORE_IDS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Store Ids'
        )->addColumn(
            'is_active',
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'Is Active'
        )->setComment(
            'Landing Page'
        );

        $installer->getConnection()->dropTable($installer->getTable('mst_search_landing_page'));
        $installer->getConnection()->createTable($table);
    }
}

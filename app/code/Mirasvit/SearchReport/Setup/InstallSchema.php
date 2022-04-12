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



namespace Mirasvit\SearchReport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\SearchReport\Api\Data\LogInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();

        $installer->startSetup();

        $table = $connection->newTable(
            $installer->getTable(LogInterface::TABLE_NAME)
        )->addColumn(
            LogInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            LogInterface::ID
        )->addColumn(
            LogInterface::QUERY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            LogInterface::QUERY
        )->addColumn(
            LogInterface::MISSPELL_QUERY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            LogInterface::MISSPELL_QUERY
        )->addColumn(
            LogInterface::FALLBACK_QUERY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            LogInterface::FALLBACK_QUERY
        )->addColumn(
            LogInterface::RESULTS,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            LogInterface::RESULTS
        )->addColumn(
            LogInterface::IP,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            LogInterface::IP
        )->addColumn(
            LogInterface::SESSION,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            LogInterface::SESSION
        )->addColumn(
            LogInterface::COUNTRY,
            Table::TYPE_TEXT,
            3,
            ['nullable' => true],
            LogInterface::COUNTRY
        )->addColumn(
            LogInterface::CUSTOMER_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            LogInterface::CUSTOMER_ID
        )->addColumn(
            LogInterface::ORDER_ITEM_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            LogInterface::ORDER_ITEM_ID
        )->addColumn(
            LogInterface::CLICKS,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            LogInterface::CLICKS
        )->addColumn(
            LogInterface::SOURCE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            LogInterface::SOURCE
        )->addColumn(
            LogInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            LogInterface::CREATED_AT
        )->addIndex(
            $installer->getIdxName(LogInterface::TABLE_NAME, [LogInterface::ID]),
            [LogInterface::ID]
        );

        $connection->dropTable($setup->getTable(LogInterface::TABLE_NAME));
        $connection->createTable($table);
    }
}

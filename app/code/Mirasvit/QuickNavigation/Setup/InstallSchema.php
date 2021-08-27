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

namespace Mirasvit\QuickNavigation\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\QuickNavigation\Api\Data\SequenceInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(SequenceInterface::TABLE_NAME)
        )->addColumn(
            SequenceInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            SequenceInterface::ID
        )->addColumn(
            SequenceInterface::STORE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            SequenceInterface::STORE_ID
        )->addColumn(
            SequenceInterface::CATEGORY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            SequenceInterface::CATEGORY_ID
        )->addColumn(
            SequenceInterface::SEQUENCE,
            Table::TYPE_TEXT,
            2048,
            ['nullable' => false],
            SequenceInterface::SEQUENCE
        )->addColumn(
            SequenceInterface::LENGTH,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            SequenceInterface::LENGTH
        )->addColumn(
            SequenceInterface::POPULARITY,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            SequenceInterface::POPULARITY
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                [SequenceInterface::STORE_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [SequenceInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                [SequenceInterface::CATEGORY_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [SequenceInterface::CATEGORY_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                [SequenceInterface::SEQUENCE],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [SequenceInterface::SEQUENCE],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                [SequenceInterface::LENGTH],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [SequenceInterface::LENGTH],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(SequenceInterface::TABLE_NAME),
                [SequenceInterface::POPULARITY],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [SequenceInterface::POPULARITY],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );

        $setup->getConnection()->dropTable($setup->getTable(SequenceInterface::TABLE_NAME));
        $setup->getConnection()->createTable($table);
    }
}

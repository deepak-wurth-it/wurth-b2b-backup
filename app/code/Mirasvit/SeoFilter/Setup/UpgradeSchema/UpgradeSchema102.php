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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Setup\UpgradeSchema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;

class UpgradeSchema102 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $connection = $setup->getConnection();

        if ($connection->isTableExists($setup->getTable('mst_seo_filter_price_rewrite'))) {
            $connection->dropTable($setup->getTable('mst_seo_filter_price_rewrite'));
        }

        if ($connection->isTableExists($setup->getTable('mst_seo_filter_rewrite'))) {
            $connection->dropTable($setup->getTable('mst_seo_filter_rewrite'));
        }

        $table = $connection->newTable(
            $setup->getTable(RewriteInterface::TABLE_NAME)
        )->addColumn(
            RewriteInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rewrite Id'
        )->addColumn(
            RewriteInterface::ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            120,
            ['nullable' => false],
            'Attribute Code'
        )->addColumn(
            RewriteInterface::OPTION,
            Table::TYPE_TEXT,
            120,
            ['nullable' => true, 'unsigned' => true],
            'Option'
        )->addColumn(
            RewriteInterface::REWRITE,
            Table::TYPE_TEXT,
            120,
            ['nullable' => false],
            'Rewrite'
        )->addColumn(
            RewriteInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'unsigned' => true],
            'Store Id'
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(RewriteInterface::TABLE_NAME),
                [RewriteInterface::REWRITE, RewriteInterface::STORE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [RewriteInterface::REWRITE, RewriteInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(RewriteInterface::TABLE_NAME),
                [RewriteInterface::ATTRIBUTE_CODE,
                 RewriteInterface::OPTION,
                 RewriteInterface::STORE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [RewriteInterface::ATTRIBUTE_CODE,
             RewriteInterface::OPTION,
             RewriteInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(RewriteInterface::TABLE_NAME),
                [RewriteInterface::STORE_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [RewriteInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(RewriteInterface::TABLE_NAME),
                [RewriteInterface::OPTION],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [RewriteInterface::OPTION],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment('SeoFilter Rewrites');

        $setup->getConnection()->createTable($table);
    }
}

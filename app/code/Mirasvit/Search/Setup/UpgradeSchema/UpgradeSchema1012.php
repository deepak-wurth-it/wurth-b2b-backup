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



namespace Mirasvit\Search\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;

class UpgradeSchema1012 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer  = $setup;
        $connection = $setup->getConnection();

        if (!$connection->isTableExists($setup->getTable(ScoreRuleInterface::INDEX_TABLE_NAME))) {
            $table = $connection->newTable(
                $setup->getTable(ScoreRuleInterface::INDEX_TABLE_NAME)
            )->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Rule ID'
            )->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Store ID'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Product ID',
            )->addColumn(
                'score_factor',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '0'],
                'Score Factor'
            )->setComment(
                'Score Rules Index'
            );

            $connection->createTable($table);
        }
    }
}

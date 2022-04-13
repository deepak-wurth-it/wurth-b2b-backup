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

class UpgradeSchema108 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer  = $setup;
        $connection = $setup->getConnection();

        $connection->dropTable($installer->getTable(ScoreRuleInterface::TABLE_NAME));

        $table = $connection->newTable(
            $setup->getTable(ScoreRuleInterface::TABLE_NAME)
        )->addColumn(
            ScoreRuleInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            ScoreRuleInterface::ID
        )->addColumn(
            ScoreRuleInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            ScoreRuleInterface::TITLE
        )->addColumn(
            ScoreRuleInterface::STORE_IDS,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            ScoreRuleInterface::STORE_IDS
        )->addColumn(
            ScoreRuleInterface::STATUS,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            ScoreRuleInterface::STATUS
        )->addColumn(
            ScoreRuleInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            ScoreRuleInterface::IS_ACTIVE
        )->addColumn(
            ScoreRuleInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            ScoreRuleInterface::IS_ACTIVE
        )->addColumn(
            ScoreRuleInterface::ACTIVE_FROM,
            Table::TYPE_DATE,
            null,
            ['nullable' => true, 'default' => null],
            ScoreRuleInterface::ACTIVE_FROM
        )->addColumn(
            ScoreRuleInterface::ACTIVE_TO,
            Table::TYPE_DATE,
            null,
            ['nullable' => true, 'default' => null],
            ScoreRuleInterface::ACTIVE_TO
        )->addColumn(
            ScoreRuleInterface::CONDITIONS_SERIALIZED,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            ScoreRuleInterface::CONDITIONS_SERIALIZED
        )->addColumn(
            ScoreRuleInterface::POST_CONDITIONS_SERIALIZED,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            ScoreRuleInterface::POST_CONDITIONS_SERIALIZED
        )->addColumn(
            ScoreRuleInterface::SCORE_FACTOR,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => '0'],
            ScoreRuleInterface::SCORE_FACTOR
        )->addIndex(
            $setup->getIdxName(ScoreRuleInterface::TABLE_NAME, [
                ScoreRuleInterface::STORE_IDS,
                ScoreRuleInterface::IS_ACTIVE,
                ScoreRuleInterface::ACTIVE_FROM,
                ScoreRuleInterface::ACTIVE_TO]),
            [ScoreRuleInterface::STORE_IDS,
             ScoreRuleInterface::IS_ACTIVE,
             ScoreRuleInterface::ACTIVE_FROM,
             ScoreRuleInterface::ACTIVE_TO]
        )->setComment(
            'Score Rules'
        );

        $connection->createTable($table);
    }
}

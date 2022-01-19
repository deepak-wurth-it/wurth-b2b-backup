<?php

namespace Amasty\Promo\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install Script
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'amasty_ampromo_rule'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_ampromo_rule'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'salesrule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Salesrule Entity Id'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Promo Products SKUs'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Promo Rule Type'
            )
            ->addIndex(
                $installer->getIdxName('amasty_ampromo_rule', ['salesrule_id']),
                ['salesrule_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_ampromo_rule',
                    'salesrule_id',
                    'salesrule',
                    'rule_id'
                ),
                'salesrule_id',
                $installer->getTable('salesrule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Autoadd Rules Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}

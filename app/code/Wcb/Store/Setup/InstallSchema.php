<?php

namespace Wcb\Store\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
                                $setup->getTable('wcb_store_pickup')
                        )->addColumn(
                                'entity_id',
                                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                null,
                                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                'Entity Id'
                        )->addColumn(
                                 'name',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                255,
                                [],
                                'Store Name'
                        )->addColumn(
                                 'image',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                255,
                                [],
                                'Store Image'
                        )->addColumn(
                                 'city',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                255,
                                [],
                                'Store City'
                         )->addColumn(
                                'state',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                255,
                                [],
                                'Store State'
                        )->addColumn(
                                 'address',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                255,
                                [],
                                'Store Address'
                        )->addColumn(
                                'status',
                                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                                null,
                                ['nullable' => false, 'default' => '0'],
                                'Status'
                        )->addColumn(
                                'created_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                'Creation Time'
                        )
                        ->addColumn(
                                'updated_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                                'Update Time'

                        )->setComment(
                'Store for pickup'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }

}

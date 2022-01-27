<?php
declare(strict_types=1);

namespace Amasty\Conditions\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddConditionsQuoteTable
{
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_conditions_quote'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_conditions_quote')
        )->addColumn(
            'id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'quote_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Quote ID'
        )->addColumn(
            'payment_code',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'default' => false],
            'Payment Code For Quote'
        )->setComment(
            'Amasty Conditions Quote Information'
        );

        $installer->getConnection()->createTable($table);
    }
}

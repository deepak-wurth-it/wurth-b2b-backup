<?php

namespace Amasty\Conditions\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class ChangeColumnDefinition
{
    public function execute(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('amasty_conditions_quote'),
            'id',
            'id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'comment' => 'ID'
            ]
        );

        $installer->getConnection()->changeColumn(
            $installer->getTable('amasty_conditions_quote'),
            'quote_id',
            'quote_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'ID'
            ]
        );
    }
}

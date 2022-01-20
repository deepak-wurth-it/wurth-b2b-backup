<?php
declare(strict_types=1);

namespace Amasty\Conditions\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddQuoteIdIndex
{
    public function execute(SchemaSetupInterface $installer): void
    {
        $installer->getConnection()->addIndex(
            $installer->getTable('amasty_conditions_quote'),
            $installer->getConnection()->getIndexName(
                $installer->getTable('amasty_conditions_quote'),
                'quote_id',
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            'quote_id'
        );
    }
}

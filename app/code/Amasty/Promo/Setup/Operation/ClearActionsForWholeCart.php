<?php
declare(strict_types=1);

namespace Amasty\Promo\Setup\Operation;

use Amasty\Promo\Model\Rule;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ClearActionsForWholeCart
{
    /**
     * Set actions empty for rules with Rule::WHOLE_CART to prevent actions validation
     *
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $emptyActions = '{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Product\\\\Combine"'
            . ',"attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}';

        $setup->getConnection()->update(
            $setup->getTable('salesrule'),
            [
                'actions_serialized' => $emptyActions
            ],
            'simple_action = ' . $setup->getConnection()->quote(Rule::WHOLE_CART)
        );
    }
}

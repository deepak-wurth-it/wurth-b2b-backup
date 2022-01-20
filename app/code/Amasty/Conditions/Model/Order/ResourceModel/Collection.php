<?php

namespace Amasty\Conditions\Model\Order\ResourceModel;

use Magento\Sales\Model\Order;

class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{

    /**
     * Calculate lifetime and average sales
     *
     * @param null $customerId
     * @param $attribute
     * @return $this
     */
    public function calculateTotalsOrder($customerId, $attribute)
    {
        $statuses = $this->_orderConfig->getStateStatuses(Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }

        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        try {
            $expr = $this->_getSalesAmountExpression();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e->getMessage());
        }

        $this->getSelect()->columns(
            ['lifetime' => "SUM({$expr})", 'average' => "AVG({$expr})"]
        );
        $this->addFieldToFilter('status', ['nin' => $statuses]);

        if ($attribute !== 'of_placed_orders') {
            $this->addFieldToFilter(
                'state',
                ['nin' => [Order::STATE_NEW, Order::STATE_PENDING_PAYMENT]]
            );
        }

        return $this->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSalesAmountExpression()
    {
        if (null === $this->_salesAmountExpression) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s - %s',
                    'arguments' => [
                        $connection->getIfNullSql('main_table.base_total_invoiced', 0),
                        $connection->getIfNullSql('main_table.base_total_refunded', 0),
                    ],
                ]
            );

            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }
}

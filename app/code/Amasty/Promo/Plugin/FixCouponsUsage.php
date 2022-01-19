<?php

namespace Amasty\Promo\Plugin;

class FixCouponsUsage
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $currentOrder;

    /**
     * @param $subject
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|array
     */
    public function beforeExecute($subject, \Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order || $order->getDiscountAmount() != 0) {
            return [$observer]; // Default Magento logic was executed
        }

        $order->setDiscountAmount(0.00001);

        if (!$this->currentOrder) {
            $this->currentOrder = $order;
        }

        return [$observer];
    }

    /**
     * @param \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver $subject
     * @param \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver $result
     * @return \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver
     */
    public function afterExecute(
        \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver $subject,
        \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver $result
    ) {
        if ($this->currentOrder) {
            $this->currentOrder->setDiscountAmount(0);
        }

        return $result;
    }
}

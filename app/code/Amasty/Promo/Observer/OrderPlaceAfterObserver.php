<?php

namespace Amasty\Promo\Observer;

use Amasty\Promo\Model\Prefix;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * event name: sales_order_place_after
 */
class OrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Prefix
     */
    private $prefix;

    public function __construct(
        Prefix $prefix
    ) {
        $this->prefix = $prefix;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        /** @var Item $item */
        foreach ($order->getAllItems() as $item) {
            $this->prefix->addPrefixToName($item);
        }
    }
}

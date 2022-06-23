<?php

namespace Wcb\Checkout\Plugin\Quote;

use Closure;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

class QuoteToOrderItem
{
    /**
     * @param ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return mixed
     */
    public function aroundConvert(
        ToOrderItem $subject,
        Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);
        $orderItem->setWcbPrice($item->getWcbPrice());
        $orderItem->setWcbAfterDiscountPrice($item->getWcbAfterDiscountPrice());
        $orderItem->setWcbDiscountPrice($item->getWcbDiscountPrice());
        return $orderItem;
    }
}

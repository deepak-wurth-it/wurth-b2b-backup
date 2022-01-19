<?php

namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items with products
 */
class Eachn extends AbstractDiscount
{
    /**
     * {@inheritdoc}
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        return $this->getPromoQtyByStep($rule, $item);
    }
}

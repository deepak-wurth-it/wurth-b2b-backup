<?php

namespace Amasty\Promo\Plugin\OfflineShipping\Model\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Calculator as ShippingCalculator;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Free Shipping for Full Discounted Promo Items
 */
class Calculator
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    private $item;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helperItem;

    /**
     * @var \Amasty\Promo\Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var array
     */
    protected $appliedShipping = [];

    public function __construct(
        \Amasty\Promo\Helper\Item $helperItem,
        \Amasty\Promo\Model\ResourceModel\Rule $ruleResource,
        \Magento\Checkout\Model\Session $resourceSession
    ) {
        $this->checkoutSession = $resourceSession;
        $this->helperItem = $helperItem;
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param ShippingCalculator $subject
     * @param ShippingCalculator $result
     * @param AbstractItem $item
     *
     * @return ShippingCalculator
     */
    public function afterProcessFreeShipping(
        ShippingCalculator $subject,
        $result,
        $item
    ) {
        $fullDiscountItems = $this->checkoutSession->getAmpromoFullDiscountItems();
        $itemSku = $this->helperItem->getItemSku($item);

        if (isset($fullDiscountItems[$itemSku])
            && $this->helperItem->isPromoItem($item)
            && $this->helperItem->getRuleId($item)
        ) {
            if (!isset($this->appliedShipping[$itemSku])) {
                $this->appliedShipping[$itemSku] = $this->ruleResource
                    ->isApplyShipping($fullDiscountItems[$itemSku]['rule_ids']);
            }

            if ($this->appliedShipping[$itemSku] === false) {
                $item->setFreeShipping(true);
            }
        }

        return $result;
    }
}

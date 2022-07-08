<?php

namespace Wcb\Checkout\Plugin\Quote;

use Closure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Store\Model\ScopeInterface;
use Wurth\Shippingproduct\Helper\Data;

class QuoteToOrderItem
{
    public $scopeConfig;

    /**
     * QuoteToOrderItem constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

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
        $productCode = $this->scopeConfig->getValue(
            Data::SHIPPING_PRODUCT_CODE,
            ScopeInterface::SCOPE_STORE,
        );
        if ($item->getProduct()->getProductCode() == $productCode) {
            $orderItem->setIsShippingProduct(1);
        }

        $orderItem->setWcbPrice($item->getWcbPrice());
        $orderItem->setWcbAfterDiscountPrice($item->getWcbAfterDiscountPrice());
        $orderItem->setWcbDiscountPrice($item->getWcbDiscountPrice());
        $orderItem->setWcbQuantityOrdered($item->getWcbQuantityOrdered());
        $orderItem->setWcbOrderUnit($item->getWcbOrderUnit());
        return $orderItem;
    }
}

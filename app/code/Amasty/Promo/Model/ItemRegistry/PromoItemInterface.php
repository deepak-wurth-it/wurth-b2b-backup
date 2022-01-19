<?php

namespace Amasty\Promo\Model\ItemRegistry;

interface PromoItemInterface
{
    public function getSku();
    public function getAllowedQty();
    public function getMinimalPrice();
    public function getDiscountItem();
    public function getDiscountAmount();
    public function getRuleId();
    public function getRuleType();
    public function setSku($sku);
    public function setAllowedQty($qty);
    public function setMinimalPrice($minimalPrice);
    public function setDiscountItem($discountItem);
    public function setDiscountAmount($discountAmount);
    public function setRuleId($ruleId);
    public function setRuleType($ruleType);
}

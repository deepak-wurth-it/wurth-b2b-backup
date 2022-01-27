<?php

namespace Amasty\Promo\Plugin\SalesRule\Model\Rule\Condition\Product;

use Amasty\Promo\Model\RuleResolver;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule;

class CombinePlugin
{
    /**
     * @var RuleResolver
     */
    private $ruleResolver;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $amHelper;

    public function __construct(
        RuleResolver $ruleResolver,
        \Amasty\Promo\Helper\Item $amHelper
    ) {
        $this->ruleResolver = $ruleResolver;
        $this->amHelper = $amHelper;
    }

    /**
     * Additional validation for ampromo rules with partial discount, when conditions enabled in rule.
     *
     * @param \Magento\Rule\Model\Condition\Combine $subject
     * @param \Closure $proceed
     * @param $type
     *
     * @return bool|mixed
     */
    public function aroundValidate(
        \Magento\Rule\Model\Condition\Combine $subject,
        \Closure $proceed,
        $type
    ) {
        if ($type instanceof Item) {
            $discountItem = $this->checkActionItem($subject->getRule(), $type);
            if ($discountItem) {
                return true;
            }
        }

        return $proceed($type);
    }

    /**
     * @param Rule $rule
     * @param Item $item
     *
     * @return bool
     */
    private function checkActionItem($rule, $item)
    {
        $action = $rule->getSimpleAction();

        if (strpos($action, "ampromo_") !== false) {
            $ampromoRule = $this->ruleResolver->getFreeGiftRule($rule);
            $isPromoItem = $this->amHelper->isPromoItem($item);

            $promoSku = $ampromoRule->getSku();
            $itemSku = $item->getProduct()->getData('sku');

            if ($isPromoItem && strpos($promoSku, $itemSku) !== false) {
                return true;
            }
        }

        return false;
    }
}

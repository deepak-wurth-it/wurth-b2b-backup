<?php

namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items for every $X spent
 */
class Spent extends AbstractDiscount
{
    /**
     * @var array
     */
    private $calculatedTotals = [];

    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $amount = max(1, $rule->getDiscountAmount());
        $step   = $rule->getDiscountStep();

        if (!$step || $this->isSkipCalculation($rule->getRuleId())) {
            return 0;
        }

        $ruleTotal = $this->getItemsSpent($this->getRuleItems($item, $rule));
        $this->setCalculatedTotals($rule->getRuleId(), $ruleTotal);
        $qty = floor($ruleTotal / $step) * $amount;
        $max = $rule->getDiscountQty();

        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    private function isSkipCalculation($ruleId)
    {
        return isset($this->calculatedTotals[$ruleId]);
    }

    /**
     * @param int $ruleId
     * @param float $total
     * @return $this
     */
    public function setCalculatedTotals($ruleId, $total)
    {
        $this->calculatedTotals[$ruleId] = $total;

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Item[] $ruleItems
     * @return float|int
     */
    protected function getItemsSpent($ruleItems)
    {
        $total = 0;
        $withDiscount = $this->config->isDiscountIncluded();
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $item */
        foreach ($ruleItems as $item) {
            if ($this->config->isTaxIncluded()) {
                $total += $item->getBaseRowTotalInclTax();
            } else {
                $total += $item->getBaseRowTotal();
            }
            if ($withDiscount) {
                $total -= $item->getBaseDiscountAmount();
            }
        }

        return $total;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return \Magento\Quote\Model\Quote\Address\Item[]
     */
    protected function getRuleItems($item, $rule)
    {
        $validItems = [];

        foreach ($this->_getAllItems($item) as $item) {
            if ($rule->getActions()->validate($item) && $rule->getRuleId() != $item->getAmpromoRuleId()) {
                $validItems[] = $item;
            }
        }
        return $validItems;
    }
}

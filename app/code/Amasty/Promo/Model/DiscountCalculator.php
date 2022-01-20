<?php

namespace Amasty\Promo\Model;

use Magento\Quote\Model\Quote\Item;

/**
 * Calculator for promo items (free gift) price discount
 */
class DiscountCalculator
{
    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Store\Model\Store $store,
        \Amasty\Promo\Model\Config $config
    ) {
        $this->store = $store;
        $this->config = $config;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Item $item
     *
     * @return float|int|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        /** @var Rule $promoRule */
        $promoRule = $rule->getAmpromoRule();
        $promoDiscount = trim($promoRule->getItemsDiscount());

        if ($item->getTaxAmount()) {
            $itemPrice = $item->getBasePriceInclTax();
        } else {
            $itemPrice = $item->getBasePrice();
        }

        switch (true) {
            case $promoDiscount === "100%":
            case $promoDiscount == "":
                $baseDiscount = $itemPrice;
                break;

            case strpos($promoDiscount, "%") !== false:
                $baseDiscount = $this->getPercentDiscount($itemPrice, $promoDiscount);
                break;

            case strpos($promoDiscount, "-") !== false:
                $baseDiscount = $this->getFixedDiscount($itemPrice, $promoDiscount);
                break;

            default:
                $baseDiscount = $this->getFixedPrice($itemPrice, $promoDiscount);
                break;
        }

        $baseDiscount = $this->getDiscountAfterMinimalPrice($promoRule, $itemPrice, $baseDiscount) * $item->getQty();

        return $baseDiscount;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        $discountAmount = $this->getBaseDiscountAmount($rule, $item) * $this->store->getCurrentCurrencyRate();

        return $discountAmount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getPercentDiscount($itemPrice, $promoDiscount)
    {
        $percent = (float)str_replace("%", "", $promoDiscount);
        $discount = $itemPrice * $percent / 100;

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedDiscount($itemPrice, $promoDiscount)
    {
        $discount = abs($promoDiscount);
        if ($discount > $itemPrice) {
            $discount = $itemPrice;
        }

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedPrice($itemPrice, $promoDiscount)
    {
        $discount = $itemPrice - (float)$promoDiscount;
        if ($discount < 0) {
            $discount = 0;
        }

        return $discount;
    }

    /**
     * @param Rule $promoRule
     * @param float $itemPrice
     * @param float $discount
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDiscountAfterMinimalPrice(Rule $promoRule, $itemPrice, $discount)
    {
        $minimalPrice = (float)$promoRule->getMinimalItemsPrice();

        if ($itemPrice > $minimalPrice && $itemPrice - $discount < $minimalPrice) {
            $discount = $itemPrice - $minimalPrice;
        }

        return $discount;
    }

    /**
     * @param array $discount
     *
     * @return bool
     */
    public function isFullDiscount($discount)
    {
        if ($discount) {
            $discountItem = $discount['discount_item'] ?? '';
            $minimalPrice = $discount['minimal_price'] ?? '';
            if ($minimalPrice) {
                return false;
            }

            return empty($discountItem) || $discountItem === "100%";
        }

        return false;
    }

    /**
     * @param $discount
     *
     * @return bool
     */
    public function isEnableAutoAdd($discount)
    {
        $addAutomatically = $this->config->getAutoAddType();

        return ($addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_ITEMS
                && $this->isFullDiscount($discount))
            || $addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_DISCOUNTED_ITEMS;
    }
}

<?php

namespace Amasty\Promo\Model\ItemRegistry;

/**
 * Promotion Item Data - record of item added by Sales Rule
 */
class PromoItemData implements PromoItemInterface
{
    private $sku;
    private $allowedQty;
    private $reservedQty = 0;
    private $minimalPrice;
    private $discountItem;
    private $discountAmount;
    private $ruleId;
    private $ruleType;
    private $isDeleted;
    private $autoAdd = false;

    /**
     * PromoItemData constructor.
     * IMPORTANT: Objects is not allowed here!
     *
     * @param string $sku
     * @param int $qty
     * @param int $ruleId
     * @param int $ruleType
     * @param float|null $minimalPrice
     * @param string|null $discountItem
     * @param float|null $discountAmount

     * @param bool $autoAdd
     */
    public function __construct(
        $sku,
        $qty,
        $ruleId,
        $ruleType,
        $minimalPrice,
        $discountItem,
        $discountAmount,
        $autoAdd
    ) {
        $this->sku = $sku;
        $this->allowedQty = $qty;
        $this->minimalPrice = $minimalPrice;
        $this->discountItem = $discountItem;
        $this->discountAmount = $discountAmount;
        $this->ruleId = $ruleId;
        $this->ruleType = $ruleType;
        $this->autoAdd = $autoAdd;
    }

    /**
     * @return array
     */
    public function getDiscountArray()
    {
        return [
            'minimal_price' => $this->getMinimalPrice(),
            'discount_item' => $this->getDiscountItem()
        ];
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Quantity assigned by rule
     *
     * @return int
     */
    public function getAllowedQty()
    {
        return $this->allowedQty;
    }

    /**
     * @param int $allowedQty
     *
     * @return $this
     */
    public function setAllowedQty($allowedQty)
    {
        $this->allowedQty = $allowedQty;

        return $this;
    }

    /**
     * Get allowed quantity of gift for add to cart
     *
     * @return int
     */
    public function getQtyToProcess()
    {
        return $this->getAllowedQty() - $this->getReservedQty();
    }

    /**
     * Quantity of current gift in the cart
     *
     * @return int
     */
    public function getReservedQty()
    {
        return $this->reservedQty;
    }

    /**
     * @param int $reservedQty
     *
     * @return $this
     */
    public function setReservedQty($reservedQty)
    {
        $this->reservedQty = $reservedQty;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinimalPrice()
    {
        return $this->minimalPrice;
    }

    /**
     * @param float $minimalPrice
     *
     * @return $this
     */
    public function setMinimalPrice($minimalPrice)
    {
        $this->minimalPrice = $minimalPrice;

        return $this;
    }

    /**
     * Is user remove item from cart manually.
     * Disable AutoAdd functionality.
     *
     * @param bool|null $flag
     *
     * @return bool
     */
    public function isDeleted($flag = null)
    {
        if ($flag !== null) {
            $this->isDeleted = $flag;
        }

        return (bool)$this->isDeleted;
    }

    /**
     * @return string
     */
    public function getDiscountItem()
    {
        return $this->discountItem;
    }

    /**
     * @param string $discountItem
     *
     * @return $this
     */
    public function setDiscountItem($discountItem)
    {
        $this->discountItem = $discountItem;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param float $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @param int $ruleId
     *
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRuleType()
    {
        return $this->ruleType;
    }

    /**
     * @param int $ruleType
     *
     * @return $this
     */
    public function setRuleType($ruleType)
    {
        $this->ruleType = $ruleType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoAdd()
    {
        return (bool)$this->autoAdd;
    }

    /**
     * @param bool $autoAdd
     *
     * @return $this
     */
    public function setAutoAdd($autoAdd)
    {
        $this->autoAdd = $autoAdd;

        return $this;
    }
}

<?php

namespace Amasty\Promo\Model\ItemRegistry;

/**
 * Class for find, filter, store and operate by set of Promotion Items Data.
 *   Promo Item Data is not a product. Its only data for operate cart item
 *   which should be added automatically or from cart.
 *
 * @since 2.5.0
 */
class PromoItemRegistry
{
    const QTY_ACTION_REPLACE = 'replace';
    const QTY_ACTION_RESERVE = 'reserve';
    /**
     * @var PromoItemFactory
     */
    private $factory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $sessionStorage;

    /**
     * @var PromoItemData[]
     */
    protected $storage = [];

    public function __construct(
        \Amasty\Promo\Model\ItemRegistry\PromoItemFactory $promoItemFactory,
        \Magento\Checkout\Model\Session $sessionStorage
    ) {
        $this->factory = $promoItemFactory;
        $this->sessionStorage = $sessionStorage;

        $this->init();
    }

    /**
     * load items from session
     */
    protected function init()
    {
        $this->storage = $this->sessionStorage->getAmpromoItems();

        if (!is_array($this->storage) || !current($this->storage) instanceof PromoItemData) {
            $this->storage = [];
        }
    }

    /**
     * Link items with session
     */
    public function save()
    {
        $this->sessionStorage->setAmpromoItems($this->getItemsForSave());
    }

    /**
     * Not all Items should be stored
     *
     * @return PromoItemData[]
     */
    public function getItemsForSave()
    {
        $items = [];
        foreach ($this->storage as $item) {
            if ($item->getAllowedQty() > 0) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param string $sku
     * @param int $qty
     * @param float|null $minimalPrice
     * @param string|null $discountItem
     * @param null $discountAmount
     * @param null $ruleId
     * @param int $ruleType
     *
     * @return PromoItemData
     */
    public function registerItem(
        $sku,
        $qty,
        $ruleId = null,
        $ruleType = \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
        $minimalPrice = null,
        $discountItem = null,
        $discountAmount = null
    ) {
        if ($item = $this->getItemBySkuAndRuleId($sku, $ruleId)) {
            //on registration qty must not be summed, only replace action
            /** @see \Amasty\Promo\Model\Rule\Action\Discount\AbstractDiscount::_getFreeItemsQty */
            $this->assignQtyToItem($qty, $item, self::QTY_ACTION_REPLACE);

            return $item;
        }

        $item = $this->factory
            ->create($sku, $qty, $ruleId, $ruleType, $minimalPrice, $discountItem, $discountAmount);

        $this->storage[] = $item;

        $this->save();

        return $item;
    }

    /**
     * Qty Action for Promo Item Data
     * Quantity Calculation process depends on Rule Type (behavior)
     * 'One of' must have same qty
     *
     * @param int|float $qty
     * @param PromoItemData|PromoItemInterface $item
     * @param string $action
     *
     * @return $this
     */
    public function assignQtyToItem($qty, PromoItemInterface $item, $action = self::QTY_ACTION_RESERVE)
    {
        if ($item->getRuleType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ONE) {
            foreach ($this->getItemsByRuleId($item->getRuleId()) as $itemData) {
                $this->qtyAction($qty, $itemData, $action);
            }
        } else {
            $this->qtyAction($qty, $item, $action);
        }

        return $this;
    }

    /**
     * @param int|float $qty
     * @param PromoItemData|PromoItemInterface $item
     * @param string $action
     */
    protected function qtyAction($qty, PromoItemInterface $item, $action)
    {
        switch ($action) {
            case self::QTY_ACTION_REPLACE:
                $item->setAllowedQty($qty);
                break;
            case self::QTY_ACTION_RESERVE:
                /*
                 * There must be summation, not replacement.
                 * Because of rule type 'one of' which have qty for group of items
                 * and reservation will be sum af added items
                 */
                $item->setReservedQty($item->getReservedQty() + $qty);
                break;
        }
    }

    /**
     * Reset reserved quantity for all items.
     * Used before recalculate reserved qty
     */
    public function resetQtyReserve()
    {
        foreach ($this->storage as $promoItemData) {
            $promoItemData->setReservedQty(0);
        }
    }

    /**
     * Remove Allowed qty for all promo data items.
     * Used before rule validation. Rules will set new allowed qty
     */
    public function resetQtyAllowed()
    {
        foreach ($this->storage as $promoItemData) {
            $promoItemData->setAllowedQty(0);
        }
    }

    /**
     * @return PromoItemData[]
     * @since 2.5.0 method added but not used
     */
    public function getGroupedItems()
    {
        $grouperItems = $result = [];
        foreach ($this->getItemsByRuleType(\Amasty\Promo\Model\Rule::RULE_TYPE_ONE) as $item) {
            $grouperItems[$item->getRuleId()][] = $item;
        }
        foreach ($grouperItems as &$items) {
            if (count($items) > 1) {
                foreach ($items as $item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @return PromoItemData[]
     */
    public function getAllItems()
    {
        return $this->storage;
    }

    /**
     * @return PromoItemData[]
     */
    public function getAllowedItems()
    {
        $items = [];
        foreach ($this->storage as $item) {
            if ($item->getQtyToProcess() > 0) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return string[]
     */
    public function getAllowedSkus()
    {
        $skuArray = [];
        foreach ($this->storage as $item) {
            if ($item->getQtyToProcess() > 0) {
                $skuArray[] = (string)$item->getSku();
            }
        }

        return $skuArray;
    }

    /**
     * @return PromoItemData[]
     */
    public function getItemsForAutoAdd()
    {
        $allowed = [];
        foreach ($this->storage as $item) {
            if ($item->getQtyToProcess() > 0 && !$item->isDeleted() && $item->isAutoAdd()) {
                $allowed[] =  $item;
            }
        }

        return $allowed;
    }

    /**
     * @param string $sku
     *
     * @return PromoItemData[]
     */
    public function getItemsBySku($sku)
    {
        $allowed = [];
        foreach ($this->storage as $item) {
            if ($item->getSku() == $sku) {
                $allowed[] =  $item;
            }
        }

        return $allowed;
    }

    /**
     * @param string $ruleId
     *
     * @return PromoItemData[]
     */
    public function getItemsByRuleId($ruleId)
    {
        $allowed = [];
        foreach ($this->storage as $item) {
            if ($item->getRuleId() == $ruleId) {
                $allowed[] =  $item;
            }
        }

        return $allowed;
    }

    /**
     * @param string $sku
     * @param string $ruleId
     *
     * @return PromoItemData|null
     */
    public function getItemBySkuAndRuleId($sku, $ruleId)
    {
        foreach ($this->storage as $item) {
            if ($item->getSku() == $sku && $item->getRuleId() == $ruleId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param int $ruleType
     *
     * @return PromoItemData[]
     */
    public function getItemsByRuleType($ruleType = \Amasty\Promo\Model\Rule::RULE_TYPE_ALL)
    {
        $allowed = [];
        foreach ($this->storage as $item) {
            if ($item->getRuleType() == $ruleType) {
                $allowed[] = $item;
            }
        }

        return $allowed;
    }
}

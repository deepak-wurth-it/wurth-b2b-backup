<?php
namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Validate and register Promo Items
 */
abstract class AbstractDiscount extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Amasty\Promo\Model\RuleResolver
     */
    protected $ruleResolver;

    protected $_itemsWithDiscount;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Model\Config $config,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Amasty\Promo\Model\RuleResolver $ruleResolver
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->promoItemHelper          = $promoItemHelper;
        $this->config                   = $config;
        $this->promoRegistry            = $promoRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleResolver = $ruleResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $this->_addFreeItems($rule, $item, $qty);

        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem|\Magento\Quote\Model\Quote\Item $item
     * @param int $qty
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addFreeItems(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $qty
    ) {
        $ampromoRule = $this->ruleResolver->getFreeGiftRule($rule);

        $promoSku = $ampromoRule->getSku();
        if (!$promoSku) {
            return;
        }

        $qty = $this->_getFreeItemsQty($rule, $item);

        if (!$qty || $this->_skip($rule, $item)) {
            return;
        }
        $discountData = [
            'discount_item' => $ampromoRule->getItemsDiscount(),
            'minimal_price' => $ampromoRule->getMinimalItemsPrice(),
        ];

        if ($ampromoRule->getType() == \Amasty\Promo\Model\Rule::RULE_TYPE_ONE) {
            $this->promoRegistry->addPromoItem(
                preg_split('/\s*,\s*/', $promoSku, -1, PREG_SPLIT_NO_EMPTY),
                $qty,
                $rule->getId(),
                $discountData,
                $ampromoRule->getType(),
                $rule->getDiscountAmount()
            );
        } else {
            $promoSku = explode(',', $promoSku);
            foreach ($promoSku as $sku) {
                $sku = trim($sku);
                if (!$sku) {
                    continue;
                }
                $this->promoRegistry->addPromoItem(
                    $sku,
                    $qty,
                    $rule->getId(),
                    $discountData,
                    $ampromoRule->getType(),
                    $rule->getDiscountAmount()
                );
            }
        }
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return int|float
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        if ($this->promoItemHelper->isPromoItem($item)) {

            return 0;
        }

        return max(1, $rule->getDiscountAmount());
    }

    /**
     * @param \Magento\SalesRule\Model\Rule                $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return bool
     */
    protected function _skip(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        if (!$this->config->getScopeValue('limitations/skip_special_price')) {
            return false;
        }

        if ($this->_itemsWithDiscount === null || count($this->_itemsWithDiscount) == 0) {
            $productIds               = [];
            $this->_itemsWithDiscount = [];

            foreach ($this->_getAllItems($item) as $addressItem) {
                $productIds[] = $addressItem->getProductId();
            }

            if (!$productIds) {
                return false;
            }

            // load products with Special Price
            $productsCollection = $this->productCollectionFactory->create()
                ->addPriceData()
                ->addAttributeToFilter('entity_id', ['in' => $productIds])
                ->addAttributeToFilter('price', ['gt' => new \Zend_Db_Expr('final_price')]);

            $this->_itemsWithDiscount = array_merge($this->_itemsWithDiscount, $productsCollection->getAllIds());
        }

        if ($this->config->getScopeValue('limitations/skip_special_price_configurable')
            && $item->getProductType() == "configurable"
        ) {
            foreach ($item->getChildren() as $child) {
                if (in_array($child->getProduct()->getId(), $this->_itemsWithDiscount)) {
                    return true;
                }
            }
        }

        if ($this->config->getScopeValue('limitations/skip_special_price')
            && $item->getProductType() == "simple"
            && in_array($item->getProductId(), $this->_itemsWithDiscount)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return \Magento\Quote\Model\Quote\Address\Item[]
     */
    protected function _getAllItems(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        return $item->getAddress()->getAllItems();
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return float|int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPromoQtyByStep(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $qty    = 0;
        $amount = max(1, $rule->getDiscountAmount());
        $step   = max(1, $rule->getDiscountStep());
        foreach ($item->getQuote()->getAllVisibleItems() as $item) {
            if (!$item || $this->promoItemHelper->isPromoItem($item) || $item->getProduct()->getParentProductId()) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                // if condition not valid for Parent, but valid for child then collect qty of child
                foreach ($item->getChildren() as $child) {
                    if ($rule->getActions()->validate($child)) {
                        $qty += $child->getTotalQty();
                    }
                }
                continue;
            }

            $qty += $item->getQty();
        }
        $item->getAddress()->setDiscountDescription($rule->getName());

        $qty = floor($qty / $step) * $amount;
        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }
}

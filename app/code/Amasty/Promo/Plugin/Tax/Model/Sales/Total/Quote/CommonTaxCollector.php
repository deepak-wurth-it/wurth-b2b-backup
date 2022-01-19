<?php

namespace Amasty\Promo\Plugin\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Amasty\Promo\Model\Storage;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector as TaxCollector;
use Magento\Tax\Model\Sales\Quote\ItemDetails;

/**
 * Tax apply for Promo Item
 */
class CommonTaxCollector
{
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
     * @var AbstractItem
     */
    private $item;

    /**
     * @var array
     */
    protected $appliedTax = [];

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
     * @param TaxCollector $subject
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param $priceIncludesTax
     * @param $useBaseCurrency
     * @param null $parentCode
     * @return array
     */
    public function beforeMapItem(
        TaxCollector $subject,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $this->item = $item;

        if ($this->isApplyTaxToFreeGift()) {
            $this->item->setNotUsePricePlugin(true);

            $this->item->setBasePrice($this->item->getProduct()->getPrice());
            $this->item->setPrice($this->item->getProduct()->getPrice());

            $this->item->setNotUsePricePlugin(false);

            $this->item->setCalculationPrice(null);
            $this->item->setBaseCalculationPrice(null);
            $this->item->setConvertedPrice(null);
        }

        return [$itemDataObjectFactory, $this->item, $priceIncludesTax, $useBaseCurrency, $parentCode];
    }

    /**
     * @param TaxCollector $subject
     * @param ItemDetails $result
     * @return mixed
     */
    public function afterMapItem(
        TaxCollector $subject,
        $result
    ) {
        if ($this->isApplyTaxToFreeGift()) {
            Storage::$cachedFreeGiftsWithTax[$result->getCode()] = $this->item->getSku();
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isApplyTaxToFreeGift()
    {
        $fullDiscountItems = $this->checkoutSession->getAmpromoFullDiscountItems();
        $itemSku = $this->helperItem->getItemSku($this->item);

        if (isset($fullDiscountItems[$itemSku]['rule_ids'])
            && $this->helperItem->isPromoItem($this->item)
        ) {
            if (!isset($this->appliedTax[$itemSku])) {
                $this->appliedTax[$itemSku] = $this->ruleResource->isApplyTax($fullDiscountItems[$itemSku]['rule_ids']);
            }

            return (bool)$this->appliedTax[$itemSku];
        }

        return false;
    }
}

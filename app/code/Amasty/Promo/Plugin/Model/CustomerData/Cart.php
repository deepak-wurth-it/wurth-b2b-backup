<?php

namespace Amasty\Promo\Plugin\Model\CustomerData;

use Amasty\Promo\Helper\Item;
use Amasty\Promo\Model\Prefix;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\CustomerData\ItemPoolInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;

class Cart
{
    /**
     * @var Item
     */
    private $promoItemHelper;

    /**
     * @var ItemPoolInterface
     */
    private $itemPoolInterface;

    /**
     * @var Url
     */
    private $catalogUrl;

    /**
     * @var Quote|null
     */
    private $quote = null;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Prefix
     */
    private $prefix;

    public function __construct(
        Item $promoItemHelper,
        ItemPoolInterface $itemPoolInterface,
        Url $catalogUrl,
        Session $checkoutSession,
        Prefix $prefix
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->itemPoolInterface = $itemPoolInterface;
        $this->catalogUrl = $catalogUrl;
        $this->checkoutSession = $checkoutSession;
        $this->prefix = $prefix;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $cart
     * @param array $sectionData
     *
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $cart, $sectionData)
    {
        $sectionData['items'] = $this->getRecentItems($cart, $sectionData);

        return $sectionData;
    }

    /**
     * Get array of last added items
     *
     * @param \Magento\Checkout\CustomerData\Cart $cart
     * @param array $sectionData
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     * @codingStandardsIgnoreStart
     */
    protected function getRecentItems(\Magento\Checkout\CustomerData\Cart $cart, $sectionData)
    {
        $items = [];
        if (!$sectionData['summary_count']) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems($cart)) as $item) {
            /** @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                if (!$this->promoItemHelper->isPromoItem($item)) {
                    $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);

                    if (isset($products[$product->getId()])) {
                        $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                        $item->getProduct()->setUrlDataObject($urlDataObject);
                    }
                }
            }
            $itemForPool = $this->itemPoolInterface->getItemData($item);
            $this->prefix->addPrefixToPoolItemName($itemForPool, $item);

            $items[] = $itemForPool;
        }

        return $items;
    }

    /**
     * Return customer quote items
     *
     * @param \Magento\Checkout\CustomerData\Cart $cart
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems(\Magento\Checkout\CustomerData\Cart $cart)
    {
        if ($cart->getCustomQuote()) {
            return $cart->getCustomQuote()->getAllVisibleItems();
        }

        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}

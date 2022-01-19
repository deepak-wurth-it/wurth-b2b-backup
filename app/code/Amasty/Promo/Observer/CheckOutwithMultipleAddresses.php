<?php

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Shows the correct prices for multi shipping checkout for promotional products.
 */
class CheckOutwithMultipleAddresses implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();

        if ($quote->getIsMultiShipping()) {
            $addresses = $quote->getAddressesCollection();

            foreach ($addresses as $address) {
                $addressItems = $address->getItemsCollection();

                foreach ($addressItems as $addressItem) {
                    /** @var \Magento\Quote\Model\Quote\Address\Item $addressItem */
                    if ($addressItem->getId()) {
                        $this->setDataToAddress($addressItem, $quote);
                    }
                }
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Item $addressItem
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function setDataToAddress($addressItem, $quote)
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $quote->getItemById($addressItem->getQuoteItemId());

        if ($quoteItem) {
            $addressItem->setCustomPrice($quoteItem->getPrice());
            $addressItem->setOriginalCustomPrice($quoteItem->getPrice());
            $addressItem->getProduct()->setIsSuperMode(true);

            if (!$addressItem->hasQty()) {
                $addressItem->setQty($quoteItem->getQty());
            }
        }
    }
}

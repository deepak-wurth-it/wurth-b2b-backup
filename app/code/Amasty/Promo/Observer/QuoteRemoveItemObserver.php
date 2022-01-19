<?php

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Mark item as deleted to prevent it's auto-addition
 *
 * event name: sales_quote_remove_item
 * observer scope: frontend, webapi_rest
 */
class QuoteRemoveItemObserver implements ObserverInterface
{

    const CHECKOUT_ROUTER = 'amasty_checkout';
    const CHECKOUT_DELETE = 'remove-item';

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry
     */
    private $promoItemRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
        $this->promoItemRegistry = $promoItemRegistry;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getQuoteItem();

        // Additional request checks to mark only explicitly deleted items
        if (($this->_request->getActionName() == 'delete'
                && $this->_request->getParam('id') == $item->getId())
            || ($this->_request->getActionName() == 'removeItem'
                && $this->_request->getParam('item_id') == $item->getId())
            || $this->isDeleteFromCheckout()
        ) {
            if (!$item->getParentId()
                && $this->promoItemHelper->isPromoItem($item)
            ) {
                $this->promoRegistry->deleteProduct($item);
            }
        }
    }

    /**
     * @return bool
     */
    private function isDeleteFromCheckout()
    {
        $queryString = $this->_request->getRequestString();

        return strpos($queryString, self::CHECKOUT_ROUTER) !== false
            && strpos($queryString, self::CHECKOUT_DELETE) !== false;
    }
}

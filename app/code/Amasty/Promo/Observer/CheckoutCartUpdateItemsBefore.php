<?php

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Mart promo data when user update qty of promo item manually
 */
class CheckoutCartUpdateItemsBefore implements ObserverInterface
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helperItem;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry
     */
    private $promoItemRegistry;

    public function __construct(
        \Amasty\Promo\Helper\Item $helperItem,
        \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry
    ) {
        $this->helperItem = $helperItem;
        $this->promoItemRegistry = $promoItemRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getInfo()->toArray();
        /** @var \Magento\Checkout\Model\Cart\CartInterface $cart */
        $cart = $observer->getCart();
        foreach ($data as $itemId => &$itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);

            if ($item && $this->helperItem->isPromoItem($item) && $itemInfo['qty'] != $item->getQty()) {
                $promoItemData = $this->promoItemRegistry->getItemBySkuAndRuleId(
                    $item->getProduct()->getData('sku'),
                    $this->helperItem->getRuleId($item)
                );
                if ($promoItemData && $promoItemData->isAutoAdd()) {
                    //disable auto add functionality if customer changing qty manually
                    $promoItemData->isDeleted(true);
                }
            }
        }
    }
}

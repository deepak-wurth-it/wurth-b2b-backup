<?php

namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * event name catalog_product_type_prepare_full_options
 *
 * Add Promotion Options to Cart Candidate.
 */
class AddPromoOptionsToCandidate implements ObserverInterface
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper
    ) {
        $this->promoItemHelper = $promoItemHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $buyRequest */
        $buyRequest = $observer->getBuyRequest();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();

        $ruleId = $this->promoItemHelper->getRuleIdFromBuyRequest($buyRequest);

        /**
         * on update qty action
         * @see \Amasty\Promo\Plugin\Quote\Item::aroundRepresentProduct
         */
        $product->setData('ampromo_rule_id', $ruleId);
    }
}

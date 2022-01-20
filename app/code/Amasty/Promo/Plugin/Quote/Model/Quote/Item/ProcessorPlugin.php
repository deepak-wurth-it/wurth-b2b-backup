<?php

namespace Amasty\Promo\Plugin\Quote\Model\Quote\Item;

/**
 * Set Item ID for correct qty validation
 */
class ProcessorPlugin
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

    /**
     * Set Request ID to promo quote item.
     * It is for correct work of reset qty.
     *
     * @param \Magento\Quote\Model\Quote\Item\Processor $subject
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Catalog\Model\Product $candidate
     */
    public function beforePrepare(
        \Magento\Quote\Model\Quote\Item\Processor $subject,
        \Magento\Quote\Model\Quote\Item $item,
        \Magento\Framework\DataObject $request,
        \Magento\Catalog\Model\Product $candidate
    ) {
        $ruleIdRequest = (int)$this->promoItemHelper->getRuleIdFromBuyRequest($request);
        $ruleIdItem = (int)$this->promoItemHelper->getRuleId($item);
        if ($ruleIdItem && $ruleIdRequest === $ruleIdItem) {
            $request->setId($item->getId());
        }
    }
}

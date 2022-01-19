<?php

namespace Amasty\Promo\Plugin\Model\GiftCard\Validator;

class Discount
{
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $item;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoHelper;

    public function __construct(\Amasty\Promo\Helper\Item $promoHelper)
    {
        $this->promoHelper = $promoHelper;
    }

    /**
     * @param \Magento\GiftCard\Model\Validator\Discount $discount
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function beforeIsValid(
        \Magento\GiftCard\Model\Validator\Discount $discount,
        $item
    ) {
        $this->item = $item;
    }

    /**
     * Define if we can apply discount to current item
     *
     * @param $result
     *
     * @return bool
     */
    public function afterIsValid($result)
    {
        if ('giftcard' === $this->item->getProductType() && $this->promoHelper->isPromoItem($this->item)) {
            return true;
        }

        return $result;
    }
}

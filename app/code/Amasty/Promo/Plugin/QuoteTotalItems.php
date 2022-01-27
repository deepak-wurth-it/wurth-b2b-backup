<?php

namespace Amasty\Promo\Plugin;

use Amasty\Promo\Api\Data\TotalsItemImageInterface;

class QuoteTotalItems
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    private $cartItemExtensionFactory;

    public function __construct(
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $cartItemExtension
    ) {
        $this->cartItemExtensionFactory = $cartItemExtension;
    }

    /**
     * @param \Magento\Quote\Model\Cart\Totals\Item $item
     * @param callable                              $proceed
     * @param                                       $attributeCode
     * @param                                       $attributeValue
     *
     * @return mixed
     */
    public function aroundSetCustomAttribute($item, callable $proceed, $attributeCode, $attributeValue)
    {
        if ($attributeCode == 'amasty_image_path' && $attributeValue instanceof TotalsItemImageInterface) {
            $extAttributes = $item->getExtensionAttributes();
            if ($extAttributes === null) {
                $extAttributes = $this->cartItemExtensionFactory->create();
            }
            $extAttributes->setAmastyPromo($attributeValue);
            $item->setExtensionAttributes($extAttributes);
            return $item;
        }
        return $proceed($attributeCode, $attributeValue);
    }
}

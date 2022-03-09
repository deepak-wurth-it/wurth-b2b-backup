<?php

namespace Wurth\Shippingproduct\Plugin;

use Magento\Quote\Model\Quote\Item;
use Wurth\Shippingproduct\Helper\Data;

class SetShippingProductIsVirtual
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * SetShippingProduct IsVirtual constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Set item as virtual
     *
     * @param Item $subject
     */
    public function afterBeforeSave(Item $subject)
    {
        if ($subject->getProduct()->getSku() == $this->helperData->getShippingProductCode()) {
            $subject->setIsVirtual(1);
        }
    }
}

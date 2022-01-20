<?php

namespace Amasty\Conditions\Plugin\Shipping\Observer;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Event\Observer;

class SaveCheckoutFieldsObserverPlugin
{
    /**
     * @var AttributeValue
     */
    private $attributeValue;

    public function __construct(AttributeValue $attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }

    /**
     * @param \Temando\Shipping\Observer\SaveCheckoutFieldsObserver $subject
     * @param Observer $observer
     *
     * @return array
     *
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Temando\Shipping\Observer\SaveCheckoutFieldsObserver $subject, Observer $observer)
    {
        /** @var \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $quoteAddress */
        $quoteAddress = $observer->getData('quote_address');

        if ($quoteAddress->getAddressType() !== \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING) {
            return [$observer];
        }

        if (!($extensionAttributes = $quoteAddress->getExtensionAttributes())) {
            return [$observer];
        }

        if (method_exists($extensionAttributes, 'getCheckoutFields')
            && !$extensionAttributes->getCheckoutFields()
        ) {
            $extensionAttributes->setCheckoutFields([$this->attributeValue]);
        }

        return [$observer];
    }
}

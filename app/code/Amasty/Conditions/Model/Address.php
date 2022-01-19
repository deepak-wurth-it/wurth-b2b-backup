<?php

namespace Amasty\Conditions\Model;

use Amasty\Conditions\Api\Data\AddressInterface;
use Magento\Framework\DataObject;

class Address extends DataObject implements AddressInterface
{
    /**
     * @param $model
     * @return bool
     */
    public function isAdvancedConditions($model)
    {
        return is_object($model->getExtensionAttributes())
            && $model->getExtensionAttributes()->getAdvancedConditions();
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->_getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddressLine()
    {
        return $this->_getData(self::SHIPPING_ADDRESS_LINE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddressLine($addressLine)
    {
        return $this->setData(self::SHIPPING_ADDRESS_LINE, $addressLine);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddressCountry()
    {
        return $this->_getData(self::BILLING_ADDRESS_COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddressCountry($billingAddressCountry)
    {
        return $this->setData(self::BILLING_ADDRESS_COUNTRY, $billingAddressCountry);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributes()
    {
        return $this->_getData(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes($customAttributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $customAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getData(self::CURRENCY);
    }

    /**
     * @param string $currency
     * @return AddressInterface|Address
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
    }
}

<?php

namespace Amasty\Conditions\Api\Data;

interface AddressInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const PAYMENT_METHOD = 'payment_method';
    const SHIPPING_ADDRESS_LINE = 'shipping_address_line';
    const CUSTOM_ATTRIBUTES = 'custom_attributes';
    const BILLING_ADDRESS_COUNTRY = 'billing_address_country';
    const CITY = 'city';
    const CURRENCY = 'currency';

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get address line
     *
     * @return string[]
     */
    public function getShippingAddressLine();

    /**
     * @param $addressLine
     *
     * @return $this
     */
    public function setShippingAddressLine($addressLine);

    /**
     * Get custom attributes
     *
     * @return string
     */
    public function getCustomAttributes();

    /**
     * @param $customAttributes
     *
     * @return $this
     */
    public function setCustomAttributes($customAttributes);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * @param $city
     *
     * @return $this
     */
    public function setCity($city);

    /**
     * Get billing address country
     *
     * @return string
     */
    public function getBillingAddressCountry();

    /**
     * @param $billingAddressCountry
     *
     * @return $this
     */
    public function setBillingAddressCountry($billingAddressCountry);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);
}

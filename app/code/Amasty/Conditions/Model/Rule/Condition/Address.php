<?php

namespace Amasty\Conditions\Model\Rule\Condition;

use Amasty\Conditions\Api\Data\AddressInterface;
use Amasty\Conditions\Model\AddressFactory;
use Amasty\Conditions\Model\Constants;
use Magento\Config\Model\Config\Source\Locale\Currency;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Payment\Model\Config\Source\Allmethods;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\StoreManagerInterface;

class Address extends AbstractCondition
{
    const CUSTOM_OPERATORS = [
        AddressInterface::SHIPPING_ADDRESS_LINE,
        AddressInterface::CITY,
        AddressInterface::CURRENCY
    ];

    const VALUE_PARTS_FOR_CONTAINS = [
        'klarna',
        'vault'
    ];

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Allmethods
     */
    private $allMethods;

    /**
     * @var \Amasty\Conditions\Model\Address
     */
    private $address;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        Country $country,
        Currency $currency,
        Allmethods $allMethods,
        \Amasty\Conditions\Model\Address $address,
        AddressFactory $addressFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->country = $country;
        $this->currency = $currency;
        $this->allMethods = $allMethods;
        $this->address = $address;
        $this->addressFactory = $addressFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            AddressInterface::BILLING_ADDRESS_COUNTRY => __('Billing Address Country'),
            AddressInterface::PAYMENT_METHOD => __('Payment Method'),
            AddressInterface::SHIPPING_ADDRESS_LINE => __('Shipping Address Line'),
            AddressInterface::CITY => __('City')
        ];

        if (version_compare($this->productMetadata->getVersion(), '2.3.0', '>=')) {
            $attributes[AddressInterface::CURRENCY] = __('Storeview currency');
        }

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOperatorSelectOptions()
    {
        if (in_array($this->getAttribute(), self::CUSTOM_OPERATORS)) {
            $operators = $this->getOperators();
            $type = $this->getInputType();
            $result = [];
            $operatorByType = $this->getOperatorByInputType();
            foreach ($operators as $operatorKey => $operatorValue) {
                if (!$operatorByType || in_array($operatorKey, $operatorByType[$type])) {
                    $result[] = ['value' => $operatorKey, 'label' => $operatorValue];
                }
            }

            return $result;
        }

        return parent::getOperatorSelectOptions();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOperators()
    {
        switch ($this->getAttribute()) {
            case AddressInterface::SHIPPING_ADDRESS_LINE:
                $result = [
                    '{}' => __('contains'),
                    '!{}' => __('does not contain')
                ];
                break;

            case AddressInterface::CITY:
                $result = [
                    '{}' => __('contains'),
                    '!{}' => __('does not contain'),
                    '==' => __('is'),
                    '!=' => __('is not'),
                    '()' => __('is one of'),
                    '!()' => __('is not one of')
                ];
                break;

            case AddressInterface::CURRENCY:
                $result = [
                    '()' => __('is one of'),
                    '!()' => __('is not one of')
                ];
                break;

            default:
                $result = [];
        }

        return $result;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case AddressInterface::SHIPPING_ADDRESS_LINE:
            case AddressInterface::CITY:
                $result = 'string';
                break;

            case AddressInterface::CURRENCY:
                $result = 'multiselect';
                break;

            default:
                $result = 'select';
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case AddressInterface::SHIPPING_ADDRESS_LINE:
            case AddressInterface::CITY:
                $result = 'text';
                break;

            case AddressInterface::CURRENCY:
                $result = 'multiselect';
                break;

            default:
                $result = 'select';
        }

        return $result;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData(Constants::VALUE_SELECT_OPTIONS)) {
            switch ($this->getAttribute()) {
                case AddressInterface::BILLING_ADDRESS_COUNTRY:
                    $options = $this->country->toOptionArray();
                    break;

                case AddressInterface::PAYMENT_METHOD:
                    $options = $this->allMethods->toOptionArray();
                    break;

                case AddressInterface::CURRENCY:
                    $options = $this->currency->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData(Constants::VALUE_SELECT_OPTIONS, $options);
        }

        return $this->getData(Constants::VALUE_SELECT_OPTIONS);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            $address = $address->getQuote()->isVirtual()
                ? $address->getQuote()->getBillingAddress()
                : $address->getQuote()->getShippingAddress();
        }

        $attrValue = $this->getAttributeValue($address);
        if (!$attrValue) {
            try {
                $attrValue = $this->getDefaultAttrValue($address);
            } catch (\Exception $e) {
                $attrValue = null;
            }
        }

        return parent::validateAttribute(trim($attrValue));
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultAttrValue(\Magento\Quote\Model\Quote\Address $address)
    {
        $attrValue = null;
        switch ($this->getAttribute()) {
            case AddressInterface::BILLING_ADDRESS_COUNTRY:
                $attrValue = $address->getCountryId();
                break;

            case AddressInterface::PAYMENT_METHOD:
                $attrValue = $address->getQuote()->getPayment()->getMethod();
                break;

            case AddressInterface::SHIPPING_ADDRESS_LINE:
                $attrValue = $address->getStreetFull();
                break;

            case AddressInterface::CITY:
                $attrValue = $address->getCity();
                break;

            case AddressInterface::CURRENCY:
                $attrValue = $address->getCurrency();
                break;
        }

        return $attrValue;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return int|mixed|null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeValue(\Magento\Quote\Model\Quote\Address $address)
    {
        $attrValue = null;
        if (!$this->address->isAdvancedConditions($address)) {
            $this->resolveAdvancedConditions($address);
        }

        if ($this->address->isAdvancedConditions($address)) {
            $advConditions = $address->getExtensionAttributes()->getAdvancedConditions();

            switch ($this->getAttribute()) {
                case AddressInterface::BILLING_ADDRESS_COUNTRY:
                case \Magento\Quote\Api\Data\AddressInterface::KEY_COUNTRY_ID:
                    $attrValue = $advConditions->getBillingAddressCountry();
                    break;

                case AddressInterface::PAYMENT_METHOD:
                    $attrValue = $advConditions->getPaymentMethod();
                    break;

                case AddressInterface::SHIPPING_ADDRESS_LINE:
                    $attrValue = $this->getStreetFull($advConditions->getAddressLine());
                    break;

                case AddressInterface::CITY:
                    $attrValue = $advConditions->getCity();
                    break;

                case AddressInterface::CURRENCY:
                    $attrValue = $advConditions->getCurrency();
                    break;
            }
        }

        return $attrValue;
    }

    /**
     * @param $address
     *
     * @return mixed|string
     */
    private function getStreetFull($address)
    {
        return is_array($address) ? implode("\n", $address) : $address;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     */
    private function resolveAdvancedConditions(\Magento\Quote\Model\Quote\Address $address)
    {
        $quote = $address->getQuote();
        if ($extensionAttributes = $address->getExtensionAttributes()) {
            $addressModel = $this->addressFactory->create();
            $address = $quote->getShippingAddress();
            if (!$address) {
                $address = $quote->getBillingAddress();
            }
            $advancedConditionData = [
                'payment_method' => $quote->getPaymentMethod(),
                'city' => $address->getCity(),
                'shipping_address_line' => $address->getStreet(),
                'custom_attributes' => $address->getCustomAttributes(),
                'billing_address_country' => $address->getCountryId(),
                'currency' => $this->storeManager->getStore()->getCurrentCurrency()->getCode()
            ];
            $addressModel->setData($advancedConditionData);

            $extensionAttributes->setAdvancedConditions($addressModel);
        }
    }

    /**
     * Fix for klarna
     * @return string
     */
    public function getValueParsed()
    {
        $valueParsed = parent::getValueParsed();
        if ($this->getAttribute() == AddressInterface::PAYMENT_METHOD && $valueParsed == 'klarna_kp') {
            $valueParsed = 'klarna_';
        }
        return $valueParsed;
    }

    /**
     * Fix for vault methods: for example - braintree called braintree_cc_vault_1 instead of braintree_cc_vault
     * @return string
     */
    public function getOperatorForValidate()
    {
        $operator = parent::getOperatorForValidate();

        $operatorsForReplace = [
            '==' => '{}',
            '!=' => '!{}'
        ];

        $replaceOperator = false;
        $valueParsed = $this->getValueParsed();

        if (is_array($valueParsed)) {
            $valueParsed = implode(',', $valueParsed);
        }

        foreach (self::VALUE_PARTS_FOR_CONTAINS as $valuePart) {
            if (strpos($valueParsed, $valuePart) !== false) {
                $replaceOperator = true;
                break;
            }
        }

        if ($this->getAttribute() == AddressInterface::PAYMENT_METHOD
            && isset($operatorsForReplace[$operator])
            && $replaceOperator
        ) {
            $operator = $operatorsForReplace[$operator];
        }
        return $operator;
    }
}

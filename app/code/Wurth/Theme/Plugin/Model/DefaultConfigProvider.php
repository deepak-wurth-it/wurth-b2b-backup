<?php

namespace Wurth\Theme\Plugin\Model;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;

class DefaultConfigProvider
{
    protected $customerRepository;

    protected $customerSession;

    protected $customerFactory;

    protected $customerAddressData;

    protected $customerAddressDataFormatter;

    public function __construct(
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        CustomerAddressDataProvider $customerAddressData,
        CustomerAddressDataFormatter $customerAddressDataFormatter
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->customerAddressData = $customerAddressData;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
    }

    public function afterGetConfig($subject, $result)
    {
        if (isset($result['customerData']['addresses'])) {
            $currentCustomer = $this->getCurrentCustomer();// add your custom here;
            $newAddress = [];
            $billingAddressId = $currentCustomer->getDefaultBilling();
            if ($currentCustomer->getCustomAttribute('customer_code')) {
                $customerCode = $currentCustomer->getCustomAttribute('customer_code')->getValue();
                $sameCustomerCodeCollection = $this->getCustomerByCustomerCode($customerCode);
                foreach ($sameCustomerCodeCollection as $_customer) {
                    $_customer = $this->customerRepository->getById($_customer->getId());
                    $newAddress = array_merge($newAddress, $this->getCustomerAddress($_customer));
                }
            }
            $newAddress = $this->sortAddressByAddressCode($newAddress);
            $newAddress = $this->changeDefaultShippingAddress($newAddress, $billingAddressId);
            $result['customerData']['addresses'] = $newAddress;
        }
        return $result;
    }

    public function getCurrentCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    public function getCustomerByCustomerCode($customerCode)
    {
        return $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("customer_code", ["eq" => $customerCode]);
    }

    public function getCustomerAddress($customer)
    {
        $customerOriginAddresses = $customer->getAddresses();
        $customerAddresses = [];
        foreach ($customerOriginAddresses as $address) {
            $addressData = $this->customerAddressDataFormatter->prepareAddress($address);
            //Set address_code as a key and value
            if (isset($addressData['custom_attributes']['address_code'])) {
                $addressData['address_code'] = $addressData['custom_attributes']['address_code']['value'];
            } else {
                $addressData['address_code'] = '00';
            }
            $customerAddresses[$address->getId()] = $addressData;
        }
        return $customerAddresses;
    }
    public function sortAddressByAddressCode($newAddress)
    {
        $addressesCode = [];
        foreach ($newAddress as $key => $val) {
            if (!isset($val['address_code'])) {
                continue;
            }
            $addressesCode[$key] = $val['address_code'];
        }
        array_multisort($addressesCode, SORT_ASC, $newAddress);
        return $newAddress;
    }
    public function changeDefaultShippingAddress($newAddress, $billingAddressId)
    {
        $updateAddress = [];
        $currentPosition = "";
        foreach ($newAddress as $key => $_address) {
            if ($billingAddressId === $_address['id']) {
                $_address["default_shipping"] = 1;
                $currentPosition = $key;
            } else {
                $_address["default_shipping"] = '';
            }
            $updateAddress[] = $_address;
        }
        // Set default shipping address position at first position
        if ($currentPosition != '') {
            $out = array_splice($updateAddress, $currentPosition, 1);
            array_splice($updateAddress, 0, 0, $out);
        }
        return $updateAddress;
    }
}

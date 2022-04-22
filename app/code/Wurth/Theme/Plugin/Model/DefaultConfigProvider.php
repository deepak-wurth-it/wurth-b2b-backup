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
            $customerAddresses[$address->getId()] = $this->customerAddressDataFormatter->prepareAddress($address);
        }
        return $customerAddresses;
    }
    public function changeDefaultShippingAddress($newAddress, $billingAddressId)
    {
        $updateAddress = [];
        foreach ($newAddress as $_address) {
            if ($billingAddressId === $_address['id']) {
                $_address["default_shipping"] = 1;
            } else {
                $_address["default_shipping"] = '';
            }
            $updateAddress[] = $_address;
        }
        return $updateAddress;
    }
}

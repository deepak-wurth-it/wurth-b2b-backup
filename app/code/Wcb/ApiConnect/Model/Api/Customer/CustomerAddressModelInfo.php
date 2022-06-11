<?php

namespace Wcb\ApiConnect\Model\Api\Customer;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Wcb\ApiConnect\Api\Customer\CustomerAddressInfo;
use Wcb\Store\Model\StoreFactory;

class CustomerAddressModelInfo implements CustomerAddressInfo
{

    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var CustomerAddressDataProvider
     */
    private $customerAddressData;
    /**
     * @var CustomerAddressDataFormatter
     */
    private $customerAddressDataFormatter;
    /**
     * @var StoreFactory
     */
    private $storeFactory;

    public function __construct(
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        CustomerAddressDataProvider $customerAddressData,
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        storeFactory $storeFactory,
        CompositeUserContext $compositeUserContext
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerAddressData = $customerAddressData;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->storeFactory = $storeFactory;
        $this->compositeUserContext = $compositeUserContext;
    }

    public function getCustomerInfo()
    {
        // if (isset($result['customerData']['addresses'])) {
         $currentCustomer = $this->getCurrentCustomer();// add your custom here;
//        print_r($currentCustomer->getEmail());
//        exit;
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
        //$isClickAndCollect = $this->checkClickAndCollect();

        //Set store pickup data
        $result['customerData']['pickup_store'] = '';
//        if ($isClickAndCollect) {
//            $newAddress = [];
//            $result['customerData']['pickup_store'] = $isClickAndCollect;
//        }

        $result['customerData']['addresses'] = $newAddress;
        //}
        return $result;
    }

    public function getCurrentCustomer()
    {
        //$this->compositeUserContext->getUserId();
        return $this->customerRepository->getById($this->compositeUserContext->getUserId());
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

//    public function checkClickAndCollect()
//    {
//        $quote = $this->checkoutSession->getQuote();
//        if ($quote->getPickupStoreId()) {
//            $pickUpStore = $this->storeFactory->create()->load($quote->getPickupStoreId());
//            if ($pickUpStore->getId()) {
//                return $pickUpStore->getData();
//            }
//        }
//        return false;
//    }
}

<?php

namespace Wurth\Theme\Plugin\Model;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Wcb\Store\Model\StoreFactory;

class DefaultConfigProvider
{
    protected $customerRepository;

    protected $customerSession;

    protected $customerFactory;

    protected $customerAddressData;

    protected $customerAddressDataFormatter;

    protected $checkoutSession;

    protected $storeFactory;

    protected $companyRepository;

    protected $findIsBillToCustomerNo = false;

    public function __construct(
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        CustomerAddressDataProvider $customerAddressData,
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        \Magento\Checkout\Model\Session $checkoutSession,
        storeFactory $storeFactory,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->customerAddressData = $customerAddressData;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->checkoutSession = $checkoutSession;
        $this->storeFactory = $storeFactory;
        $this->companyRepository = $companyRepository;
    }

    public function afterGetConfig($subject, $result)
    {
        if (isset($result['customerData']['addresses'])) {
            $currentCustomer = $this->getCurrentCustomer();// add your custom here;
            // $newAddress = [];

            //get Customer address using main user id only
            $company = $this->getCompany($currentCustomer);
            $customer = $this->customerRepository->getById($company->getSuperUserId());
            $newAddress = $this->getCustomerAddress($customer);
            $billingAddressId = $customer->getDefaultBilling();

            // commented code is Get all adddress using all customer_code

            /*if ($currentCustomer->getCustomAttribute('customer_code')) {
                $customerCode = $currentCustomer->getCustomAttribute('customer_code')->getValue();
                $sameCustomerCodeCollection = $this->getCustomerByCustomerCode($customerCode);
                foreach ($sameCustomerCodeCollection as $_customer) {
                    $_customer = $this->customerRepository->getById($_customer->getId());
                    $newAddress = array_merge($newAddress, $this->getCustomerAddress($_customer));
                }
            }
            */
            $newAddress = $this->sortAddressByAddressCode($newAddress);
            $newAddress = $this->changeDefaultShippingAddress($newAddress, $billingAddressId);
            $isClickAndCollect = $this->checkClickAndCollect();

            //Set store pickup data
            $result['customerData']['pickup_store'] = '';
            if ($isClickAndCollect) {
                $newAddress = [];
                $result['customerData']['pickup_store'] = $isClickAndCollect;
            }

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
            // if found bill to customer no
            if ($address->getCustomAttribute('bill_to_customer_code') &&
                $address->getCustomAttribute('is_bill_to_customer_number')) {
                $isBillToCustomerNumber = $address->getCustomAttribute("is_bill_to_customer_number")->getValue();
                $billToCustomerCode = $address->getCustomAttribute("bill_to_customer_code")->getValue();
                if ($isBillToCustomerNumber && $billToCustomerCode) {
                    $this->findIsBillToCustomerNo = true;
                    continue;
                }
            }

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
        // if found bill to customer no
        if ($this->findIsBillToCustomerNo) {
            foreach ($newAddress as $_address) {
                if (isset($_address['custom_attributes']['is_company_main_address'])) {
                    $isCompanyDefaultBillingAddress = $_address['custom_attributes']['is_company_main_address']['value'];
                    if ($isCompanyDefaultBillingAddress) {
                        $billingAddressId = $_address['id'];
                        break;
                    }
                }
            }
        }

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
    public function checkClickAndCollect()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getPickupStoreId()) {
            $pickUpStore = $this->storeFactory->create()->load($quote->getPickupStoreId());
            if ($pickUpStore->getId()) {
                return $pickUpStore->getData();
            }
        }
        return false;
    }
    public function getCompany($customer)
    {
        try {
            if ($customer->getExtensionAttributes()->getCompanyAttributes()) {
                $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                return $this->companyRepository->get($companyId);
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

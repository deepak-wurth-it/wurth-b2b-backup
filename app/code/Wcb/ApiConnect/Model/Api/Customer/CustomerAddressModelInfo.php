<?php

namespace Wcb\ApiConnect\Model\Api\Customer;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Wcb\ApiConnect\Api\Customer\CustomerAddressInfo;
use Wcb\Store\Model\StoreFactory;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

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
    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryInterface;

    public function __construct(
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        CustomerAddressDataProvider $customerAddressData,
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        storeFactory $storeFactory,
        CompositeUserContext $compositeUserContext,
        CompanyRepositoryInterface $companyRepository,
        AddressRepositoryInterface $addressRepositoryInterface
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerAddressData = $customerAddressData;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->storeFactory = $storeFactory;
        $this->compositeUserContext = $compositeUserContext;
        $this->companyRepository = $companyRepository;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
    }

    public function getCustomerInfo()
    {
        //if (isset($result['customerData']['addresses'])) {
         $currentCustomer = $this->getCurrentCustomer();// add your custom here;
        //$newAddress = [];

        //get Customer address using main user id only
        $company = $this->getCompany($currentCustomer);
        $customer = $this->customerRepository->getById($company->getSuperUserId());
        $newAddress = $this->getCustomerAddress($customer);
        $billingAddressId = $customer->getDefaultBilling();

        $newAddress = $this->sortAddressByAddressCode($newAddress);
        $newAddress = $this->changeDefaultShippingAddress($newAddress, $billingAddressId);


//        $billingAddressId = $currentCustomer->getDefaultBilling();
//        if ($currentCustomer->getCustomAttribute('customer_code')) {
//            $customerCode = $currentCustomer->getCustomAttribute('customer_code')->getValue();
//            $sameCustomerCodeCollection = $this->getCustomerByCustomerCode($customerCode);
//            foreach ($sameCustomerCodeCollection as $_customer) {
//                $_customer = $this->customerRepository->getById($_customer->getId());
//                $newAddress = array_merge($newAddress, $this->getCustomerAddress($_customer));
//            }
//        }
//        $newAddress = $this->sortAddressByAddressCode($newAddress);
//        $newAddress = $this->changeDefaultShippingAddress($newAddress, $billingAddressId);
        //$isClickAndCollect = $this->checkClickAndCollect();

        //Set store pickup data
        $result['customerData']['pickup_store'] = '';
        $result['customerData']['email'] = $currentCustomer->getEmail();
//        if ($isClickAndCollect) {
//            $newAddress = [];
//            $result['customerData']['pickup_store'] = $isClickAndCollect;
//        }

        $result['customerData']['addresses'] = $newAddress;
        $result['customerData']['company_detail']= $this->getDefaultBillAddress();
        //}
        return $result;
    }

    public function getCurrentCustomer()
    {
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


    /**
     * @return array|bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
//    public function getDefaultBillAddress_old()
//    {
//        $currentCustomer = $this->getCurrentCustomer();
//        if ($currentCustomer->getId()) {
//            $customer = $this->customerRepository->getById($currentCustomer->getId());
//            $billingAddressId = $customer->getDefaultBilling();
//
//            try {
//                $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
//
//                $company = $this->getCompany($customer);
//                $customerCode = '';
//                $companyName = '';
//                if ($company) {
//                    $companyName = $company->getCompanyName();
//                }
//                if ($customer->getCustomAttribute("customer_code")) {
//                    $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
//                }
//
//                $companyName .= " (" . $customerCode . ")";
//                $addressData = [];
//                $addressData['name'] = $companyName;
//                $addressData['city'] = $billingAddress->getCity();
//                $addressData['street'] = $billingAddress->getStreet();
//                $addressData['postcode'] = $billingAddress->getPostCode();
//                $addressData['id'] = $billingAddressId;
//                return $addressData;
//            } catch (Exception $e) {
//                return false;
//            }
//        }
//        return false;
//    }


    /**
     * @return array|bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultBillAddress()
    {
        $currentCustomer = $this->getCurrentCustomer();
        //$customerSession = $this->customerSession->create();
        if ($currentCustomer->getId()) {
            try {
                $customer = $this->customerRepository->getById($currentCustomer->getId());
                $company = $this->getCompany($customer);
                // get Super user using current user
                $customer = $this->customerRepository->getById($company->getSuperUserId());
                $billingAddressId = $customer->getDefaultBilling();
                $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
                $customerCode = '';
                $companyName = '';
                if ($company) {
                    $companyName = $company->getCompanyName();
                }
                if ($customer->getCustomAttribute("customer_code")) {
                    $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
                }

                $companyName .= " (" . $customerCode . ")";
                $addressData = [];
                $addressData['name'] = $companyName;
                $addressData['city'] = $billingAddress->getCity();
                $addressData['street'] = $billingAddress->getStreet();
                $addressData['postcode'] = $billingAddress->getPostCode();
                $addressData['id'] = $billingAddressId;
                return $addressData;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $customer
     * @return bool|CompanyInterface
     */
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

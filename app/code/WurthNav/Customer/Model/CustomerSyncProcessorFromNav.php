<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Customer\Model;

use WurthNav\Customer\Model\ResourceModel\ShopContactFactory as ResourceShopContactFactory;

use Psr\Log\LoggerInterface;
use WurthNav\Customer\Model\CustomersFactory as NavCustomers;


/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerSyncProcessorFromNav
{

    protected $customer;
    protected $navCustomerObj;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \WurthNav\Customer\Model\ShopContactFactory    $shopContactFactory,
        ResourceShopContactFactory    $resourceShopContactFactory,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        NavCustomers $navCustomers,
        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->shopContactFactory = $shopContactFactory;
        $this->resourceShopContactFactory = $resourceShopContactFactory;
        $this->customerFactory = $customerFactory;
        $this->companyManagement = $companyManagement;
        $this->addressRepository = $addressRepositoryInterface;
        $this->addressFactory = $addressFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->logger = $logger;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->navCustomers = $navCustomers;
        $this->addressDataFactory = $addressDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getBillingAddressByCustomerCode($customerCode, $currentCustomerId)
    {
        $customerObj = $this->customerCollectionFactory->create();
        $collection = $customerObj->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_code', $customerCode)
            ->load();
        if ($collection->getSize()) {

            $firstItem = $collection->getFirstItem();
            $customerId = $firstItem->getId();
            $billingAddress =  $this->getDefaultBillingAddress($customerId);
            if ($billingAddress) {
                $firstName =     $billingAddress->getFirstname();
                $lastName =      $billingAddress->getLastname();
                $countryId =     $billingAddress->getCountryId();
                $regionId =      $billingAddress->getRegionId();
                $regionName =     $billingAddress->getRegion();
                $city =         $billingAddress->getCity();
                $postcode =     $billingAddress->getPostcode();
                $street =         $billingAddress->getStreet();
                $telephone =     $billingAddress->getTelephone();

                $address = $this->addressDataFactory->create();
                $address->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setCountryId($countryId)
                    ->setRegionId($regionId)
                    ->setRegion($regionName)
                    ->setCity($city)
                    ->setPostcode($postcode)
                    ->setCustomerId($currentCustomerId)
                    ->setStreet($street)
                    ->setTelephone($telephone)
                    ->setIsDefaultBilling('1');
                //->setSaveInAddressBook('1');
                $address->save();
                $log .= "Saved customer billing details".PHP_EOL;
                return true;
            }
        }
        return false;
    }


    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {   //$this->getBillingAddressByCustomerCode('12345');
        $log = "";
        $this->customer = '';
        $this->navCustomerObj = "";
        $navCustomerObj = $this->navCustomers->create();

        $collection = $navCustomerObj->getCollection()
            ->addFieldToFilter('Synchronized', array('eq' => '0'));
        $status = [];


        $x = 0;
        if ($collection->getSize() && $collection->count()) {


            foreach ($collection as $navCustomer) {


                try {
                    $webSiteId = $this->storeManager->getStore()->getWebsiteId();
                    $storeId = $this->storeManager->getStore()->getStoreId();;
                    $email =  $navCustomer->getData('Email');
                    $email = trim($email);
                    /****************  save customer **********************/
                   
                    $CustomerModel = $this->customerFactory->create();
                    $CustomerModel->setWebsiteId($webSiteId);
                    $CustomerModel->loadByEmail($email);
                    $customerId = $CustomerModel->getId();
                    if(empty($customerId)){
                        continue;
                    }
                    $customerRepoObject = $this->customerRepository->getById($customerId);
                    $firstName = $CustomerModel->getFirstname();
                    $lastName = $CustomerModel->getLastname();
                    if ($customerRepoObject) {

                        $Email = $navCustomer->getData('Email');
                        if ($Email) {
                            $customerRepoObject->setEmail($Email);
                        }
                        $Phone = $navCustomer->getData('Phone');
                        if ($Phone) {
                            $customerRepoObject->setCustomAttribute('phone', $Phone);
                        }

                        $CustomerCode = $navCustomer->getData('CustomerCode');

                        if ($CustomerCode) {
                            $customerRepoObject->setCustomAttribute('customer_code', $CustomerCode);
                        }

                        $this->customerRepository->save($customerRepoObject);
                        $log = "Saved customer basic details".PHP_EOL;
                    }


                    // $Disabled = $navCustomer->getData('Disabled');
                    // if ($Disabled) {
                    //     $CustomerModel->setIsActive($Disabled);
                    // }


                    /********************** Update Address  ********************/
                    $shippingAddress = $this->getDefaultShippingAddress($customerId);

                    if ($shippingAddress) {
                        $shippingId =  $shippingAddress->getId();
                        $street = $navCustomer->getData('Address');
                        $PostalCode = $navCustomer->getData('PostalCode');
                        $City = $navCustomer->getData('City');
                        $Phone = $navCustomer->getData('Phone');
                        $BillToCustomerNo = $navCustomer->getData('BillToCustomerNo');

                        $region = $shippingAddress->getRegion();
                        $regionId = $shippingAddress->getRegionId();
                        $countryId = $shippingAddress->getCountryId();
                        $shippingAddress->setCustomerId($customerId)
                            ->setId($shippingId)
                            ->setFirstname($firstName)
                            ->setLastname($lastName)
                            ->setCountryId($countryId)
                            ->setRegionId($regionId)
                            ->setRegion($region)
                            ->setCity($City)
                            ->setPostcode($PostalCode)
                            ->setStreet([$street])
                            ->setTelephone($Phone)
                            ->setIsDefaultShipping(true);
                            $log .= "Saved customer shipping details".PHP_EOL;
    
                        if ($BillToCustomerNo) {
                            $this->getBillingAddressByCustomerCode($BillToCustomerNo, $customerId);
                        }

                        $address = $this->addressRepository->save($shippingAddress);
                        if ($address->getId()) {
                            $status[] = $address->getId();
                        }
                    }



                    /********************* company data  ***********************/
                    $companyId = $this->companyManagement->getByCustomerId($customerId)->getId();
                    $company = $this->companyRepository->get($companyId);
                    $SalespersonCode = $navCustomer->getData('SalespersonCode');
                    $company->setSalesRepresentativeId($SalespersonCode);
                    $BranchCode = $navCustomer->getData('BranchCode');
                    $company->setDivision($BranchCode);
                    $savedCompany = $this->companyRepository->save($company);
                    $log .= "Saved company  details".PHP_EOL;

                    //$CustomerType = $navCustomer->getData('CustomerType');
                    //$Potential = $navCustomer->getData('Potential');
                    //$AdvancePaymentRequired = $navCustomer->getData('AdvancePaymentRequired');//Ignore as per scope
                    //$CustomerDiscountGroup = $navCustomer->getData('CustomerDiscountGroup');//Ignore as per scope
                    //$CentralOfficeCustomerCode = $navCustomer->getData('CentralOfficeCustomerCode');//Ignore as per scope
                    //$BillToCustomerNo = $navCustomer->getData('BillToCustomerNo');
                    echo $log;
                    if ($savedCompany->getId()) {
                        $status[] = $savedCompany->getId();
                    }
                    if (count($status) == 3) {
                        $navCustomer->setData('Synchronized', '1');
                        $navCustomer->save();
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
    }


    public function getDefaultShippingAddress($customerId)
    {
        try {
            $address = $this->accountManagement->getDefaultShippingAddress($customerId);
        } catch (NoSuchEntityException $e) {
            return __('You have not added default shipping address. Please add default shipping address.');
        }
        return $address;
    }
    public function getDefaultBillingAddress($customerId)
    {
        try {
            $address = $this->accountManagement->getDefaultBillingAddress($customerId);
        } catch (NoSuchEntityException $e) {
            return __('You have not added default billing address. Please add default billing address.');
        }
        return $address;
    }
}

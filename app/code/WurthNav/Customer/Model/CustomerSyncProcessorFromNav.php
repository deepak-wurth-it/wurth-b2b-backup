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
    public $log;
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
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,

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
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->navCustomers = $navCustomers;
        $this->addressDataFactory = $addressDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getBillingAddressByCustomerCode($customerCode, $currentCustomerId, $billingAddress)
    {

        $navCustomerObj = $this->navCustomers->create();
        $customerNavAddress =  $navCustomerObj->load('CustomerCode', $customerCode);


        if ($customerNavAddress->getData('CustomerCode') == $customerCode) {
            $street = [];
            $city =        $customerNavAddress->getData('City');
            $postcode =      $customerNavAddress->getData('PostalCode');
            $street[$customerNavAddress->getData('Address')];
            $telephone =     $customerNavAddress->getData('Phone');

            if ($city && $postcode &&  !empty($street) && $telephone) {


                $regionName = $billingAddress->getRegion();
                $regionId = $billingAddress->getRegionId();
                $countryId = $billingAddress->getCountryId();
                $firstNameBiller =     $billingAddress->getFirstname();
                $lastNameBiller =      $billingAddress->getLastname();
                $address = $this->addressDataFactory->create();
                $address->setFirstname($firstNameBiller)
                    ->setLastname($lastNameBiller)
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
                $this->log .= "Saved customer billing details" . PHP_EOL;
                return true;
            }
        }
        return false;
    }


    public function getCustomerTokenLocal($customerId)
    {

        $customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($customerId)->getToken();
        return $tokenKey;
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

        $collection->getSelect()
            ->joinLeft(
                ['Branches' => 'Branches'],
                'main_table.BranchCode = Branches.Code',
                [
                    'parentBranchCode' => 'Branches.ParentBranch'
                ]
            );
        $status = [];


        $x = 0;
        //echo $collection->getSize();exit;
        if ($collection->getSize() && $collection->count()) {


            foreach ($collection as $navCustomer) {


                try {
                    $webSiteId = $this->storeManager->getStore()->getWebsiteId();
                    $storeId = $this->storeManager->getStore()->getStoreId();;
                    $email =  $navCustomer->getData('Email');
                    $email = trim($email);
                    /****************  save customer **********************/

                    $customerObject = $this->customerFactory->create();
                    $customerObject->setWebsiteId($webSiteId);
                    $customerObject->loadByEmail($email);
                    $customerId = $customerObject->getId();



                    if (empty($customerId)) {
                        continue;
                    }
                    //echo $customerId;exit;
                    $customerRepoObject = $this->customerRepository->getById($customerId);
                    $firstName = $customerObject->getFirstname();
                    $lastName = $customerObject->getLastname();

                    //print_r(get_class_methods($customerObject->getDataModel()));exit;
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

                        $customerRepoObject->setCustomAttribute('verified', true);
                        $customerRepoObject->setConfirmation(null);
                        $this->customerRepository->save($customerRepoObject);

                        $this->getCustomerTokenLocal($customerId);
                        $Disabled = $navCustomer->getData('Disabled');
                        $customerObject->setIsActive($Disabled);
                        $customerObject->setStatus(1);
                        $customerObject->save();

                        $this->log .= "Saved customer basic details" . PHP_EOL;
                    }





                    /********************** Update customer Address  ********************/
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
                        $firstNameShipper =     $shippingAddress->getFirstname();
                        $lastNameShipper =      $shippingAddress->getLastname();
                        $shippingAddress->setCustomerId($customerId)
                            ->setId($shippingId)
                            ->setFirstname($firstNameShipper)
                            ->setLastname($lastNameShipper)
                            ->setCountryId($countryId)
                            ->setRegionId($regionId)
                            ->setRegion($region)
                            ->setCity($City)
                            ->setPostcode($PostalCode)
                            ->setStreet([$street])
                            ->setTelephone($Phone)
                            ->setIsDefaultShipping(true);
                        $this->log .= "Saved customer shipping details" . PHP_EOL;
                        $address = $this->addressRepository->save($shippingAddress);
                        if ($address->getId()) {
                            $status[] = $address->getId();
                        }
                    }

                    #====================== Customer Customer Billing Data ==================#
                    $billingAddress =  $this->getDefaultBillingAddress($customerId);

                    if ($BillToCustomerNo) {
                        $statusBillToData = $this->getBillingAddressByCustomerCode($BillToCustomerNo, $customerId, $billingAddress);
                    }

                    if ($billingAddress && $statusBillToData == false) {

                        $billingId =  $billingAddress->getId();
                        $street = $navCustomer->getData('Address');
                        $postcode = $navCustomer->getData('PostalCode');
                        $city = $navCustomer->getData('City');
                        $telephone = $navCustomer->getData('Phone');
                        $BillToCustomerNo = $navCustomer->getData('BillToCustomerNo');

                        $regionName = $billingAddress->getRegion();
                        $regionId = $billingAddress->getRegionId();
                        $countryId = $billingAddress->getCountryId();
                        $firstNameBiller =     $billingAddress->getFirstname();
                        $lastNameBiller =      $billingAddress->getLastname();

                        $address = $this->addressDataFactory->create();
                        $address->setCustomerId($customerId)
                            ->setId($billingId)
                            ->setFirstname($firstNameBiller)
                            ->setLastname($lastNameBiller)
                            ->setCountryId($countryId)
                            ->setRegionId($regionId)
                            ->setRegion($regionName)
                            ->setCity($city)
                            ->setPostcode($postcode)
                            ->setStreet($street)
                            ->setTelephone($telephone)
                            ->setIsDefaultBilling('1');
                        $address->save();
                        $this->log .= "Saved customer billing details" . PHP_EOL;
                        return true;
                    }



                    #====================== Customer Billing End ===================#

                    /********************* company data  ***********************/
                    $companyId = $this->companyManagement->getByCustomerId($customerId)->getId();
                    $company = $this->companyRepository->get($companyId);

                    $SalespersonCode = $navCustomer->getData('SalespersonCode');
                    $company->setWcbSalesPersonCode($SalespersonCode);
                    if ($navCustomer->getData('Name')) {
                        $company->setName($navCustomer->getData('Name'));
                    }
                    $BranchCode = $navCustomer->getData('BranchCode');
                    $company->setDivision($BranchCode);
                    //$company->setActivities($BranchCode);
                    $company->setStatus('1');
                    $savedCompany = $this->companyRepository->save($company);
                    $this->log .= "Saved company  details" . PHP_EOL;


                    // Customer Update in WurthNav ERP
                    $navCustomer->setData('Synchronized', '1');
                    $navCustomer->save();
                    $this->wurthNavLogger($this->log);
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                    $this->wurthNavLogger($e->getMessage());
                    $this->wurthNavLogger("Customer Could not save,Please see Customer ID =>> " . $customerId);
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

    public function wurthNavLogger($log)
    {
        echo $log;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_customer_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
}

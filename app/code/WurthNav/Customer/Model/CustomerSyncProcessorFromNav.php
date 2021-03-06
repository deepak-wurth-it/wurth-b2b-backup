<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Customer\Model;

use WurthNav\Customer\Model\ResourceModel\ShopContactFactory as ResourceShopContactFactory;

use Psr\Log\LoggerInterface;
use WurthNav\Customer\Model\CustomersFactory as NavCustomers;
use Magento\Company\Model\ResourceModel\Customer as CustomerResource;


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
        \Magento\Customer\Model\Group $groupModel,
        NavCustomers $navCustomers,
        CustomerResource $customerResource,
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
        $this->companyRepository = $companyRepository;
        $this->logger = $logger;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->navCustomers = $navCustomers;
        $this->addressDataFactory = $addressDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupModel = $groupModel;
        $this->customerResource = $customerResource;
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
                $address = $this->addressRepository->save($address);
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
    {   
        $log = "";
        $this->customer = '';
        $this->navCustomerObj = "";
        $navCustomerObj = $this->navCustomers->create();

        $collection = $navCustomerObj->getCollection();

        $collection->getSelect()
            ->joinLeft(
                ['bnch' => 'Branches'],
                'main_table.BranchCode  = bnch.Code',
                [
                    'parentBranchCode' => 'bnch.ParentBranch'
                ]
            )->where("main_table.Synchronized  = 0");
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

                    $customerObject = $this->customerFactory->create();
                    $customerObject->setWebsiteId($webSiteId);
                    $customerObject->loadByEmail($email);
                    $customerId = $customerObject->getId();



                    if (empty($customerId)) {
                        continue;
                    }
                  
                    $customerRepoObject = $this->customerRepository->getById($customerId);
                    $firstName = $customerObject->getFirstname();
                    $lastName = $customerObject->getLastname();

                    #================= Magento Customer Data ==========================#
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
                        $customerType = $navCustomer->getData('CustomeType');
                        $customerRepoObject->setCustomAttribute('verified', true);
                        $customerRepoObject->setCustomAttribute('wc_customer_type', $customerType);
                        $customerRepoObject->setConfirmation(null);
                        $this->getCustomerTokenLocal($customerId);
                        $Disabled = $navCustomer->getData('Disabled');
                        $customerObject->setIsActive($Disabled);
                        $customerObject->setStatus(1);

                        $this->customerRepository->save($customerRepoObject);

                        $this->log .= "Saved customer basic details" . PHP_EOL;
                    }

                    #================= Magento Customer Data ==========================#


                    #====================== Customer Customer Billing Data ==================#
                    $billingAddress =  $this->getDefaultBillingAddress($customerId);
                    $BillToCustomerNo = $navCustomer->getData('BillToCustomerNo');
                    $statusBillToData = false;
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
                            ->setStreet([$street])
                            ->setTelephone($telephone)
                            ->setIsDefaultBilling('1');
                            
                        $address = $this->addressRepository->save($address);
                        $this->log .= "Saved customer billing details" . PHP_EOL;
                        
                    }



                    #====================== Customer Billing End ===================#



                    #====================== Customer Company ========================#
                    $companyId = $this->companyManagement->getByCustomerId($customerId)->getId();
                    $company = $this->companyRepository->get($companyId);
                    $existingGroup  = "";
					//print_r(get_class_methods($company));exit;
                    $SalespersonCode = $navCustomer->getData('SalespersonCode');
                    $company->setWcbSalesPersonCode($SalespersonCode);
                    
                    if ($navCustomer->getData('Name')) {
                        $company->setCompanyName($navCustomer->getData('Name'));
                    }


                    // Setting group id and parent id
                    if ($navCustomer->getData('parentBranchCode')) {
                        $parentBranchCode  = $navCustomer->getData('parentBranchCode');
                        $company->setDivision($parentBranchCode);
                        $existingGroup = $this->groupModel->load($parentBranchCode, 'parent_branch');
                       
                        if($existingGroup->getId()){
                             $company->setCustomerGroupId($existingGroup->getId()); // Company Group id
                             $superUserId = $company->getSuperUserId();
                             $superUser = $this->customerRepository->getById($superUserId); //Super User Setting group id
                             $superUser->setGroupId($existingGroup->getId());
							 $this->customerRepository->save($superUser);
							
                             $customerIds = $this->customerResource->getCustomerIdsByCompanyId($company->getId());//Sub users setting group id
								foreach ($customerIds as $customerId) {
									$customer = $this->customerRepository->getById($customerId);
									$customer->setGroupId($existingGroup->getId());
									$this->customerRepository->save($customer);
								}
                        }
                    }

                    $BranchCode = $navCustomer->getData('BranchCode');
                    $company->setActivities($BranchCode);
                    $company->setStatus('1');
                    $company->save();
                    $savedCompany = $this->companyRepository->save($company);
                    $this->log .= "Saved company  details" . PHP_EOL;

                    #====================== Customer Company ========================#

                    // Customer Update in WurthNav ERP
                    $navCustomer->setData('Synchronized', '1');
                    $navCustomer->save(); //ERP customer table update
                    $customerObject->save(); //Magento 2 Customer SAVE
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

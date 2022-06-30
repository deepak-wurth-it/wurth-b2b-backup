<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Customer\Model;

use WurthNav\Customer\Model\ResourceModel\ShopContactFactory as ResourceShopContactFactory;

use Psr\Log\LoggerInterface;

/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerSyncProcessor
{

    protected $customer;
    public $log;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \WurthNav\Customer\Model\ShopContactFactory    $shopContactFactory,
        ResourceShopContactFactory    $resourceShopContactFactory,
        \Magento\Company\Api\CompanyManagementInterface $companyRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,


        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->shopContactFactory = $shopContactFactory;
        $this->resourceShopContactFactory = $resourceShopContactFactory;
        $this->customerFactory = $customerFactory;
        $this->companyRepository = $companyRepository;
        $this->addressRepository = $addressRepositoryInterface;
        $this->addressFactory = $addressFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerRepository = $customerRepository;
        $this->companyRepositoryInterface = $companyRepositoryInterface;
        $this->_resourceConnection = $resourceConnection;

        $this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
        $this->connectionDefault  = $this->_resourceConnection->getConnection();
        $this->logger = $logger;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {


        $customer = $this->customerFactory->create();

        $collection = $customer->getCollection()
            ->addAttributeToSelect('*');

        $collection->getSelect()
            ->joinLeft(
                ['company' => 'company'],
                'e.entity_id = company.super_user_id',
                [
                    'company_name' => 'company.company_name',
                    'super_user_id' => 'company.super_user_id'
                ]
            );
        //)->where(
        //'e.sync_status = ?',
        //'0'
        // );

        $x = 0;

        if ($collection->getSize() && $collection->count()) {

            foreach ($collection as $customer) {
                $this->customer = '';
                $data = [];
                $company = '';
                $OIB = '';
                $telephone = '';
                $i = '';

                //print_r(get_class_methods($customer));exit;
                try {
                    if ($customer->getId() == "") {
                        continue;
                    }
                    $this->customer = $customer;
                    $billingAddressId = $customer->getDefaultBilling();
                    $shippingAddressId = $customer->getDefaultShipping();
                    $address = $customer->getDefaultBillingAddress();
                    $getDefaultBillingAddress = $customer->getDefaultBillingAddress();
                    $getDefaultShippingAddress = $customer->getDefaultShippingAddress();
                    $company = $this->getCustomerCompany($customer->getId());
                    $shopContactFactory = $this->shopContactFactory->create();
                    $customerType =  $customer->getSuperUserId() ? '0' : '1';
                    $customerCode = $customer->getData('customer_code');
                    $customerEmail = $customer->getEmail();
                    $newsletter = $this->isCustomerSubscribeByEmail($customerEmail) ? '1' : '0';

                    $customerRepoObject = $this->customerRepository->getById($customer->getId());


                    #================ About the company & Address =========================#
                    if ($company && empty($customerType)) {

                        $dataDimensionCode2 = $this->getDimensionCode($company->getActivities());

                        $companyRepoObject = $this->companyRepositoryInterface->get($company->getId());
                        $shopContactFactory->setData('Company Name', $company->getCompanyName());
                        $shopContactFactory->setData('Employees', $company->getNumberOfEmployees());
                        $shopContactFactory->setData('Global Dimension 2 Code',  $dataDimensionCode2);
                        //$shopContactFactory->setData('Global Dimension 1 Code', $company->getDivision());//not required
                        $shopContactFactory->setData('Country', $company->getCountryId());

                        $address = '';
                        $addressTemp = $company->getStreet();
                        if ($addressTemp) {
                            $address = implode(",", $addressTemp);
                        }

                        $shopContactFactory->setData('Address', $address);
                        $shopContactFactory->setData('City', $company->getCity());
                        $shopContactFactory->setData('Post Code',  $company->getPostcode());
                        $shopContactFactory->setData('Region', $company->getRegionId());
                        $shopContactFactory->setData('VAT Registration No_', $companyRepoObject->getData('vat_tax_id'));
                    }

                    #=================  About the user  ===========================# 
                    if ($customerRepoObject && $customer) {

                        $shopContactFactory->setData('No_', $customer->getId());

                        $shopContactFactory->setData('Name', $customer->getName());

                        $shopContactFactory->setData('E-Mail', $customer->getEmail());


                        if ($customer->getCustomAttribute("position")) {
                            $position = $customer->getCustomAttribute("position")->getValue();
                            $shopContactFactory->setData('position', $position);
                        }


                        $superUser = $customer->getSuperUserId() ? '' : $customer->getId();

                        $shopContactFactory->setData('Customer No_', $superUser);

                        $shopContactFactory->setData('Type', $customerType);

                        $shopContactFactory->setData('Newsletter', $newsletter);

                        $shopContactFactory->setCustomAttribute('verified', true);

                        if ($customer->getCustomAttribute("position")) {
                            $position = $customer->getCustomAttribute("position")->getValue();
                            $shopContactFactory->setData('Job Title', $position);
                        }




                        if ($customerRepoObject->getCustomAttribute('phone')) {
                            $data['mobile_phone_no'] = $data['Phone No_'] = $customerRepoObject->getCustomAttribute('phone')->getValue();
                            $shopContactFactory->setData('Mobile Phone No_', $data['mobile_phone_no']);
                        }


                        //If user is not admin

                        if ($customerType) {
                            echo $customerCode . PHP_EOL;
                            $shopContactFactory->setData('Customer No_', $customerCode);
                        }
                    }
                    #================//////////////////==========================#

                    #================= User Shipping =============================#

                    if ($billingAddressId && $getDefaultShippingAddress && empty($customerType)) {
                        $data['Ship To Post Code'] =  $getDefaultShippingAddress ? $getDefaultShippingAddress->getPostcode() : '';
                        $data['Ship To City'] =  $getDefaultShippingAddress ? $getDefaultShippingAddress->getCity() : '';
                        $data['Ship To Address'] =  $shippingAddressId ? implode(',', $this->addressRepository->getById($shippingAddressId)->getStreet()) : '';
                        $shopContactFactory->setData('Ship To Post Code', $data['Ship To Post Code']);
                        $shopContactFactory->setData('Ship To City', $data['Ship To City']);
                        $shopContactFactory->setData('Ship To Address', $data['Ship To Address']);
                    }
                    #==================///////////////=============================#

                    #================= User Billing =============================#
                    if ($billingAddressId && $getDefaultShippingAddress && empty($customerType)) {
                        $data['Invoice To Post Code'] = $getDefaultBillingAddress ? $getDefaultBillingAddress->getPostcode() : '';
                        $data['Invoice To City'] =  $getDefaultBillingAddress ? $getDefaultBillingAddress->getCity() : '';
                        $data['Invoice To Address'] =  $billingAddressId ? implode(',', $this->addressRepository->getById($billingAddressId)->getStreet()) : '';

                        $shopContactFactory->setData('Invoice To Post Code', $data['Invoice To Post Code']);
                        $shopContactFactory->setData('Invoice To City', $data['Invoice To City']);
                        $shopContactFactory->setData('Invoice To Address', $data['Invoice To Address']);
                    }
                    #==================///////////////=============================#

                    //$data['Mobile Phone No_'] = $telephone; //Customer Mobile

                    $shopContactFactory->setData('needs_update', '1');

                    if ($customer->getId() && $shopContactFactory->getData()) {
                        $shopContactFactory2 = $this->shopContactFactory->create();
                        $shopContactExist = $shopContactFactory2->load($customer->getId(), 'No_');



                        if ($shopContactExist->getId() && $shopContactExist->getData('needs_update') == '0') {
                            $shopContactFactory->setId($shopContactExist->getId());
                            $shopContactFactory->save();
                            $this->log .= 'Updated Record Successfully for customer id ' . $customer->getName() . PHP_EOL;
                        } else if (is_null($shopContactExist->getData('needs_update')) && empty($shopContactExist->getId())) {
                            $shopContactFactory->save();
                            $this->log .=  'Insert Record Successfully for customer id ' . $customer->getName() . PHP_EOL;
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    echo $e->getMessage() . PHP_EOL;
                    continue;
                }
            }
            $this->wurthNavLogger($this->log);
        }
    }

    public function getCustomerCompany($customerId)
    {

        $company = $this->companyRepository->getByCustomerId($customerId);
        return $company;
    }

    public function getFullShippingAddress($getDefaultShippingAddress)
    {
        foreach ($getDefaultShippingAddress as $data) {
            //echo $data;
        }
    }

    public function getFullBillingAddress($getDefaultBillingAddress)
    {
        foreach ($getDefaultBillingAddress as $data) {
            //echo $data;
        }
    }

    public function isAccountConfirmed($customerId)
    {
        return $this->accountManagement->getConfirmationStatus($customerId);
    }

    public function isCustomerSubscribeById($customerId)
    {
        $status = $this->subscriberFactory->create()->loadByCustomerId((int)$customerId)->isSubscribed();

        return (bool)$status;
    }

    public function isCustomerSubscribeByEmail($email)
    {
        $status = $this->subscriberFactory->create()->loadByEmail($email)->isSubscribed();

        return (bool)$status;
    }

    public function getDimensionCode($name)
    {
        $dataDimensionCode2 = '';
        if ($name) {
            $tableDivision = $this->connectionDefault->getTableName('division');
            $select = $this->connectionDefault->select()
                ->from(
                    ['d' => $tableDivision],
                    ['*']
                )->where("d.name = '$name'");
            $data = $this->connectionDefault->fetchOne($select);
            $dataDimensionCode2 =   $data ?? $data['branch_code'];
        }
        return $dataDimensionCode2;
    }

    public function wurthNavLogger($log)
    {
        echo $log;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_customer_sync.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
}

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
        $this->logger = $logger;


    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
        $this->customer = '';
        $customer = $this->customerFactory->create();

        $collection = $customer->getCollection()
            ->addAttributeToSelect('*');
        $collection->getSelect() ->where(
            'sync_status = ?',
            '0'
        );

      
        $x = 0;
        if ($collection->getSize() && $collection->count()) {

            foreach ($collection as $customer) {
            try{
                $billingAddressId = $customer->getDefaultBilling();
                $shippingAddressId = $customer->getDefaultShipping();

                $address = $customer->getDefaultBillingAddress();
                $getDefaultBillingAddress = $customer->getDefaultBillingAddress();
                $getDefaultShippingAddress = $customer->getDefaultShippingAddress();
                $company = $this->getCustomerCompany($customer->getId());

                if(empty($billingAddressId) || empty($shippingAddressId) && empty($company)){
                    continue;
                }
                
                $updateData='';
                $saveData='';
                $key='';
                $value='';
               
                $shopContactFactory = $this->shopContactFactory->create();

                $shopContact = $shopContactFactory->getCollection();
                $shopContact->getSelect()->where(
                    'No_ = ?',
                    $customer->getId()
                );
              
               
                //print_r(($this->isAccountConfirmed($customer->getId())));

                //Customer detail =====================

               
                $id =  $customer->getId();
                $Name = $customer->getName();
                $email = $customer->getEmail();
                $newsLetterOptStatus = 2;
                if($this->isCustomerSubscribeById($id)){
                    $newsLetterOptStatus = 1;
                }
               
               //Company Data ==========================
                $company = '';
                $companyName = '';
                $division = '';
                $activites = '';
                $companyPhone = '';
                $jobTitle = '';
                $vatId_OIB = '';
               
                if($company){
                        $vatId_OIB = $this->companyRepositoryInterface->get($company->getId())->getVatTaxId();
                        $customerCompanyAttributes = $this->customerRepository->getById($customer->getId())->getExtensionAttributes()->getCompanyAttributes();
                        $companyPhone =  $company->getTelephone();
                        $companyName = $company->getCompanyName();
                        $activites = $company->getExtensionAttributes()->getActivities();
                        $division = $company->getExtensionAttributes()->getDivision();
                        $jobTitle = $customerCompanyAttributes->getJobTitle();

                }
               
                //Billing Data ============================
                $invoice_postcode='';
                $invoice_city = '';
                $invoice_street = '';
                $billing_street_address='';

                if ($getDefaultBillingAddress) {
                   
                    $invoice_postcode =  $getDefaultBillingAddress->getPostcode();
                }

                if ($getDefaultBillingAddress) {
                    $invoice_city =  $getDefaultBillingAddress->getCity();
                }

              

                if($billingAddressId){
                   $billing_street_address =  implode(',',$this->addressRepository->getById($billingAddressId)->getStreet());
                }

                //Shipping Data ==============================

               
                $ship_city = '';
                $ship_postcode = '';
                $ship_street = '';
                $ship_street_address='';
                
                if ($getDefaultShippingAddress) {
                    $ship_postcode =  $getDefaultShippingAddress->getPostcode();
                }

                

                if ($getDefaultShippingAddress) {
                    $ship_city =  $getDefaultShippingAddress->getCity();
                }

              
                if($shippingAddressId){
                    $ship_street_address =  implode(',',$this->addressRepository->getById($shippingAddressId)->getStreet());
                 }


               // Address ========================================

               $city = '';
               $country='';
               $contact='';
               $postcode = '';
               $street = '';
               $telephone = '';
               
                if ($address) {
                   
                    $postcode =  $address->getPostcode();
                }

                if ($address) {
                    $city =  $address->getCity();
                }

                if ($address) {
                    $country =  $address->getCountryId();
                }


                if ($address) {
                    
                    $telephone =  $address->getTelephone();
                }

                if ($address) {
                    $street =   implode(',',$address->getStreet());
                   
                }

              
                $data = array(
                'No_' => $id,
                'Name'=>$Name,
                'Address'=>$street,
                'Post Code'=> $postcode,
                'City'=> $city,
                'Country'=>$country,
                'Mobile Phone No_'=>$telephone,//Customer Mobile
                'E-Mail'=>$email,
                'Salesperson Code'=>'',// Balnk as discussed with BA
                'VAT Registration No_'=>$vatId_OIB,//oib
                'Job Title'=>$jobTitle,//Postiton in company
                'Type'=>'1',//0 = New or 1 = Existing User//If customer code will come then type 1 then 0
                'Company No_'=>'',//Empty
                'Company Name'=>$companyName,//Company name
                'Contact No_'=>$contact,
                'Customer No_'=>$id,
                'Cust_ Business Unit Code'=>'',//Empty
                'Status'=>'',//Blank as discussed with BA
                'Phone No_'=>$companyPhone,
                'Global Dimension 1 Code'=>$division,//From Company
                'Global Dimension 2 Code'=>$activites,// From Company
                'Ship To Address'=>$ship_street_address,
                'Invoice To Address'=>$billing_street_address,
                'Send Invoice Via E-mail'=>'',//Empty(will be handled by ERP)
                'Ship To Post Code'=>$ship_postcode,
                'Ship To City'=>$ship_city,
                'Invoice To Post Code'=>$invoice_postcode,
                'Invoice To City'=>$invoice_city,
                'Newsletter'=>$newsLetterOptStatus);
               
                if ($shopContact->getData()){
                    foreach($data as $key=>$value){
                        if($value){
                           $shopContact->getFirstItem()->setData($key,$value);
                        }    
                    }
                   
                    $updateData = $shopContact->save();
                }else{
                    $shopContactFactory->addData($data);
                    $saveData = $shopContactFactory->save();
                }

               
               
                if($saveData){
                    echo  __('Insert Record Successfully for customer id '.$customer->getName().' !').PHP_EOL ;
                }
                if($updateData){
                    echo  __('Updated Record Successfully for customer id '.$customer->getName().' !').PHP_EOL ;
                }
               
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    echo $e->getMessage().PHP_EOL;
                    continue;
                }
            }

        }
    }

    public function getCustomerCompany($customerId)
    {
        
        $company = $this->companyRepository->getByCustomerId($customerId);
        return $company;
    }

    public function getFullShippingAddress ($getDefaultShippingAddress){
        foreach($getDefaultShippingAddress as $data){
            //echo $data;
        }

    }

    public function getFullBillingAddress ($getDefaultBillingAddress){
        foreach($getDefaultBillingAddress as $data){
            //echo $data;
        }
    }
    
    public function isAccountConfirmed($customerId)
    {
        return $this->accountManagement->getConfirmationStatus($customerId);
    }

    public function isCustomerSubscribeById($customerId) {
        $status = $this->subscriberFactory->create()->loadByCustomerId((int)$customerId)->isSubscribed();

        return (bool)$status;
    }




}

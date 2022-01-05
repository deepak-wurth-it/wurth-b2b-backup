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
        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->shopContactFactory = $shopContactFactory;
        $this->resourceShopContactFactory = $resourceShopContactFactory;
        $this->customerFactory = $customerFactory;

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
               
                $address = $customer->getDefaultBillingAddress();
                $getDefaultBillingAddress = $customer->getDefaultBillingAddress();
                $getDefaultShippingAddress = $customer->getDefaultShippingAddress();

                $postcode = '';
                $city = '';
                $country='';
                $contact='';
                $ship_postcode = '';
                $ship_city = '';
                $invoice_postcode='';
                $invoice_city = '';
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
               
                if ($address) {
                    $postcode =  $address->getPostcode();
                }

                if ($getDefaultShippingAddress) {
                    $ship_postcode =  $getDefaultShippingAddress->getPostcode();
                }

                if ($getDefaultBillingAddress) {
                    $invoice_postcode =  $getDefaultBillingAddress->getPostcode();
                }

                if ($getDefaultShippingAddress) {
                    $ship_city =  $getDefaultShippingAddress->getCity();
                }

                if ($getDefaultBillingAddress) {
                    $invoice_city =  $getDefaultBillingAddress->getCity();
                }

                if ($address) {
                    $city =  $address->getCity();
                }

                if ($address) {
                    $country =  $address->getCountryId();
                }


                if ($address) {
                    $contact =  $address->getCountryId();
                }


                $data = array(
                'No_' => $customer->getId(),
                'Name'=>$customer->getName(),
                'Address'=>json_encode($address),
                'Post Code'=> $postcode,
                'City'=> $city,
                'Country'=>$country,
                'Mobile Phone No_'=>$contact,
                'E-Mail'=>$customer->getEmail(),
                'Salesperson Code'=>'',// Balnk As of now
                'VAT Registration No_'=>'',//oib
                'Job Title'=>'',//Postiton in company
                'Type'=>'1',//0 = New or 1 = Existing User
                'Company No_'=>123,//If type 1 company no will go else blank
                'Company Name'=>'Company Name1',//Company name
                'Contact No_'=>$contact,
                'Customer No_'=>$customer->getId(),
                'Cust_ Business Unit Code'=>'',//Blank
                'Status'=>'',//Blank
                'Phone No_'=>$contact,
                'Global Dimension 1 Code'=>'',//Division
                'Global Dimension 2 Code'=>'',//Activity
                'Ship To Address'=>json_encode($getDefaultShippingAddress),
                'Invoice To Address'=>json_encode($getDefaultBillingAddress),
                'Send Invoice Via E-mail'=>'',//blank
                'Ship To Post Code'=>$ship_postcode,
                'Ship To City'=>$ship_city,
                'Invoice To Post Code'=>$invoice_postcode,
                'Invoice To City'=>$invoice_city);
               
                if ($shopContact->getData()){
                   //print_r(get_class_methods($shopContact));exit;
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
                //print_r($shopContact->debug());exit;
                //print_r(get_class_methods($shopContact));exit;
            }

        }
    }


}

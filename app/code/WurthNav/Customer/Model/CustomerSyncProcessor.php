<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Customer\Model;

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
        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->shopContactFactory = $shopContactFactory;
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
                
             

                $shopContact = $this->shopContactFactory->create();

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
                'Salesperson Code'=>'',
                'VAT Registration No_'=>'',
                'Job Title'=>'',
                'Type'=>'1',
                'Company No_'=>123,
                'Company Name'=>'Company Name',
                'Contact No_'=>$contact,
                'Customer No_'=>$customer->getId(),
                'Cust_ Business Unit Code'=>'',
                'Status'=>'',
                'Phone No_'=>$contact,
                'Global Dimension 1 Code'=>'',
                'Global Dimension 2 Code'=>'',
                'Ship To Address'=>json_encode($getDefaultShippingAddress),
                'Invoice To Address'=>json_encode($getDefaultBillingAddress),
                'Send Invoice Via E-mail'=>$customer->getEmail(),
                'Ship To Post Code'=>$ship_postcode,
                'Ship To City'=>$ship_city,
                'Invoice To Post Code'=>$invoice_postcode,
                'Invoice To City'=>$invoice_city);
               
               
                $shopContact->addData($data);
                $saveData = $shopContact->save();
                if($saveData){
                    echo  __('Insert Record Successfully !') ;
                }
                //print_r($shopContact->debug());exit;
                //print_r(get_class_methods($shopContact));exit;
            }

        }
    }


}

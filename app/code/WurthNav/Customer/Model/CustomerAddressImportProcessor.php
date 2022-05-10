<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Customer\Model;

use Psr\Log\LoggerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Model\GroupFactory;
use \Magento\Customer\Model\CustomerFactory;
use \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use \Magento\Customer\Model\AddressFactory;
use \Magento\Customer\Api\AccountManagementInterface;





/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressImportProcessor
{
	const INDEXER_LIST = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

	const UNITS_OF_MEASURE = 'unitsofmeasure';

	protected $product;
	protected $connectionWurthNav;
	protected $connectionDefault;
	protected $groupFactory;

	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		GroupInterfaceFactory $groupFactory,
		GroupRepositoryInterface $groupRepository,
		GroupFactory $groupFactoryModel,
		CustomerFactory $customerFactory,
		CustomerCollectionFactory $CustomerCollectionFactory,
		AddressFactory $addressFactory,
		AccountManagementInterface $accountManagement,
		LoggerInterface $logger
	) {
		$this->_resourceConnection = $resourceConnection;
		$this->logger = $logger;
		$this->groupFactory = $groupFactory;
		$this->customerFactory = $customerFactory;
		$this->customerCollectionFactory = $CustomerCollectionFactory;
		$this->groupRepository = $groupRepository;
		$this->groupFactoryModel = $groupFactoryModel;
		$this->addressFactory = $addressFactory;
        $this->accountManagement = $accountManagement;

		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();
	}

	/**
	 * @param array $fixtures
	 * @throws \Exception
	 */
	public function install()
	{
		try {
			$select = $this->connectionWurthNav->select()
				->from(['c' => 'CustomerDeliveryAddress']);
			$data = $this->connectionWurthNav->fetchAll($select);
			
			if (count($data)) {
				foreach ($data as $row) {
					try {
						$region = "";
						$regionId = "";
						$countryId = "";
						$lastName = "";
						$customerObj = $this->customerCollectionFactory->create();
						$collection = $customerObj->addAttributeToSelect('*')
						->addAttributeToFilter('customer_code',$row['CustomerCode'])
						->load();
						if(empty($collection->getSize())){
							continue;
							
						}			
						$dataCustomer = $collection->getFirstItem();
						
						$customerId = $dataCustomer->getId(); 
						$address = $this->addressFactory->create();
						
						
						$shippingAddress = $this->getDefaultShippingAddress($customerId);
						if($shippingAddress){
							$region = $shippingAddress->getRegion();
							$regionId = $shippingAddress->getRegionId();
							$countryId = $shippingAddress->getCountryId();
					    }
                        
						$firstName = $row['Name'];
						$street = $row['Address']; 
						$city = $row['City']; 
						$postCode = $row['PostalCode']; 
						//?? = $row['Contact']; 
					    $telephone = $row['PhoneNo']; 
					
						if($firstName && $street && $city && $postCode && $postCode ){
							
							$log = '__CUSTOMER_DELIVERY_ADDRESS__IMPORT__DATA_EMPTY_ISSUE_FOR_CUSTOMER_CODE'.$row['CustomerCode'];
							$this->wurthNavLogger($log);
							continue;
						}
						$address->setCustomerId($customerId)
							->setFirstname($firstName)
							->setLastname($lastName)
							->setCountryId($countryId)
                            //->setRegionId($regionId)
                            //->setRegion($region)
							->setPostcode($postCode)
							->setCity($city)
							->setTelephone($telephone)
							//->setFax($fax)
							//->setCompany($company)
							->setStreet([$street])
							//->setIsDefaultBilling('0')
							//->setIsDefaultShipping('0')
							->setSaveInAddressBook('1');
							if($address->save()){
							 $log = '__CUSTOMER_DELIVERY_ADDRESS__IMPORT__ : Customer address  has been saved for customer code'.$dataCustomer->getCustomerCode();
							}else{
							  $log = '__CUSTOMER_DELIVERY_ADDRESS__IMPORT__ : Customer address could not be saved for customer code'.$dataCustomer->getCustomerCode();
							}
							
							$this->wurthNavLogger($log);
						

					} catch (\Exception $e) {
						$log = '--------------------- __CUSTOMER_DELIVERY_ADDRESS__IMPORT_ERROR__------------------------' . PHP_EOL;
						$log = $e->getMessage() . PHP_EOL;
						$this->wurthNavLogger($log);
					}
				}
			}
		} catch (\Exception $e) {
			$log = '---------------------__CUSTOMER_DELIVERY_ADDRESS__IMPORT_ERROR__------------------------' . PHP_EOL;
			$log = $e->getMessage() . PHP_EOL;
			$this->wurthNavLogger($log);
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
		echo $log.PHP_EOL;
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurth_nav_logger.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

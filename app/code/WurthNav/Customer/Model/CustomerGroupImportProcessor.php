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
class CustomerGroupImportProcessor
{
    CONST INDEXER_LIST = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

    const UNITS_OF_MEASURE = 'unitsofmeasure';

	protected $product;
	protected $connectionWurthNav;
	protected $connectionDefault;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
            \Magento\Framework\App\ResourceConnection $resourceConnection,
			LoggerInterface $logger
	)
		{
		$this->_resourceConnection = $resourceConnection;
        $this->logger = $logger;
       }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
		$parent = ['A', 'B', 'C', 'D', 'G', 'I', 'M', 'T'];
		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();

		$select = $this->connectionWurthNav->select()
        ->from(
            ['branches' => 'Branches']
        )->where(
            ['branches' => 'Branches']
        );
       $data = $this->connectionWurthNav->fetchAll($select);

       if(count($data)){
		foreach($data as $row){
		  $tableName = $this->connectionDefault->getTableName(self::UNITS_OF_MEASURE);
		
			print_r($row);exit;
			
		}
	}
		
    }

  

}


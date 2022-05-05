<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pim\Product\Model;
use Psr\Log\LoggerInterface;


/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnitsOfMeasureProcessor
{
    CONST INDEXER_LIST = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

    const UNITS_OF_MEASURE = 'unitsofmeasure';

	protected $product;
	protected $connectionPim;
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
		$this->connectionPim = $this->_resourceConnection->getConnection('pim');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();

		$select = $this->connectionPim->select()
        ->from(
            ['uom' => 'unitsofmeasure']
        );
       $data = $this->connectionPim->fetchAll($select);

       if(count($data)){
		foreach($data as $row){
		  $tableName = $this->connectionDefault->getTableName(self::UNITS_OF_MEASURE);
		
			$data = [
			'unitsofmeasure_id' => $row['Id'],
			'code' =>$row['Code'],
			'name' =>$row['Name'],
			'active' =>$row['Active'],
			'created_at' =>$row['CreatedDate'],
			'modifiedate_at' =>$row['ModifiedDate'],
			'external_id' =>$row['ExternalId']
			];
			
			
			$selectExist = $this->connectionPim->select()
			->from(
				['uom' => 'unitsofmeasure']
			)
			->where('unitsofmeasure_id = ?', $row['Id']);
			
		     $dataExist = $this->connectionDefault->fetchOne($selectExist);

			
			if(empty($dataExist)){
				$this->connectionDefault->insert(self::UNITS_OF_MEASURE, $data);
			}
				if(!empty($dataExist)){
				$where = ['unitsofmeasure_id = ?' => (int)$dataExist];
				
				$this->connectionDefault->update(self::UNITS_OF_MEASURE, $data,$where);

			}
			
		}
	}
		
    }

  

}


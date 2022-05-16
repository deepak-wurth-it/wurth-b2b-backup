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
class EmployeesProcessor
{

    const EMPLOYEES = 'wurthnav_employees';

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
		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();

		$select = $this->connectionWurthNav->select()
        ->from(
            ['emp' => 'Employees']
        );
       $data = $this->connectionWurthNav->fetchAll($select);

       if(count($data)){
		foreach($data as $row){
			
		  $tableName = $this->connectionDefault->getTableName(self::EMPLOYEES);
		
			$data = [
			'EmployeeCode' => $row['EmployeeCode'],
			'Name' =>$row['Name'],
			'Email' =>$row['Email'],
			'PhoneNo' =>$row['PhoneNo'],
			'BackofficeSupportEmployee' =>$row['BackofficeSupportEmployee'],
			'AreaManagerCode' =>$row['AreaManagerCode'],
			'RegionalManagerCode' =>$row['RegionalManagerCode']
			];
			
			
			$selectExist = $this->connectionDefault->select()
			->from(
				['emp' => self::EMPLOYEES ]
			)
			->where('EmployeeCode = ?', $row['EmployeeCode']);
			
		     $dataExist = $this->connectionDefault->fetchOne($selectExist);

		
			if(empty($dataExist)){
				$this->connectionDefault->insert(self::EMPLOYEES, $data);
			}
				if(!empty($dataExist)){
				$where = ['EmployeeCode = ?' => (int)$dataExist];
				
				$this->connectionDefault->update(self::EMPLOYEES, $data,$where);

			}
			
		}
	}
		
    }

  

}


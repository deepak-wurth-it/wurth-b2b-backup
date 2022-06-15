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
	public $log;
	protected $connectionWurthNav;
	protected $connectionDefault;

	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		LoggerInterface $logger
	) {
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

		if (count($data)) {
			foreach ($data as $row) {
				try {
					$tableName = $this->connectionDefault->getTableName(self::EMPLOYEES);

					$data = [
						'EmployeeCode' => $row['EmployeeCode'],
						'Name' => $row['Name'],
						'Email' => $row['Email'],
						'PhoneNo' => $row['PhoneNo'],
						'BackofficeSupportEmployee' => $row['BackofficeSupportEmployee'],
						'AreaManagerCode' => $row['AreaManagerCode'],
						'RegionalManagerCode' => $row['RegionalManagerCode']
					];


					$selectExist = $this->connectionDefault->select()
						->from(
							['emp' => self::EMPLOYEES]
						)
						->where('EmployeeCode = ?', $row['EmployeeCode']);

					$dataExist = $this->connectionDefault->fetchOne($selectExist);


					if (empty($dataExist)) {
						$this->connectionDefault->insert(self::EMPLOYEES, $data);
						$this->log .= 'Employee has been added for emplooyee code =>>' . $row['EmployeeCode'] . PHP_EOL;
					}
					if (!empty($dataExist)) {

						$where = ['EmployeeCode = ?' => (int)$dataExist];

						$this->connectionDefault->update(self::EMPLOYEES, $data, $where);
						$this->log .= 'Employee has been update for emplooyee code =>>' . $row['EmployeeCode'] . PHP_EOL;
					}
				} catch (\Exception $e) {
					$this->logger->critical($e->getMessage());
				}
				$this->wurthNavLogger($this->log);
			}
		}
	}


	public function wurthNavLogger($log)
	{
		echo $log . PHP_EOL;
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_employee_import.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

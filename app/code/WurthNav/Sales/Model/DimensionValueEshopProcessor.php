<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;


/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DimensionValueEshopProcessor
{

	const WURTH_NAV_DIMENSION_VALUE_ESHOP = 'wurthnav_dimension_value_eshop';
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
				['dve' => 'DimensionValue_Eshop']
			);
		$data = $this->connectionWurthNav->fetchAll($select);

		if (count($data)) {
			foreach ($data as $row) {
				try {
					$tableName = $this->connectionDefault->getTableName(self::WURTH_NAV_DIMENSION_VALUE_ESHOP);

					$data = [
						'Code' => $row['Code'],
						'DimensionCode' => $row['DimensionCode'],
						'Name' => $row['Name']
					];


					$selectExist = $this->connectionDefault->select()
						->from(
							['wdve' => self::WURTH_NAV_DIMENSION_VALUE_ESHOP]
						)
						->where('Code = ?', $row['Code']);

					$dataExist = $this->connectionDefault->fetchOne($selectExist);


					if (empty($dataExist)) {
						$this->connectionDefault->insert(self::WURTH_NAV_DIMENSION_VALUE_ESHOP, $data);
					}
					if (!empty($dataExist)) {
						$where = ['Code = ?' => (int)$dataExist];

						$this->connectionDefault->update(self::WURTH_NAV_DIMENSION_VALUE_ESHOP, $data, $where);
					}
					$this->log .= 'Dimension code has been imported/updated =>>' . $row['Code'] . PHP_EOL;
				} catch (\Exception $e) {
					$this->logger->info($e->getMessage());
					echo $e->getMessage() . PHP_EOL;
					continue;
				}
			}
		}
		$this->wurthNavLogger($this->log);
        // No Synchronized or need_update  Other validation field found for previous  done lines

	}


	public function wurthNavLogger($log = null)
	{
		echo $log;
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_dimension_value_import.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

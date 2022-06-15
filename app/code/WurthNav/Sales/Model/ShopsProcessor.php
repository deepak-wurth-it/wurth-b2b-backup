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
class ShopsProcessor
{

	const WURTH_NAV_SHOPS = 'wurthnav_shops';

	protected $connectionWurthNav;
	protected $connectionDefault;
	public $log;
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
				['shp' => 'Shops']
			);
		$data = $this->connectionWurthNav->fetchAll($select);

		if (count($data)) {
			foreach ($data as $row) {
				try {
					$tableName = $this->connectionDefault->getTableName(self::WURTH_NAV_SHOPS);

					$data = [
						'Code' => $row['Code'],
						'Name' => $row['Name'],
						'Address' => $row['Address'],
						'City' => $row['City'],
						'PostCode' => $row['PostCode'],
						'Wholesale Center Location' => $row['Wholesale Center Location'],
						'E-Mail' => $row['E-Mail']
					];


					$selectExist = $this->connectionDefault->select()
						->from(
							['wns' => self::WURTH_NAV_SHOPS]
						)
						->where('Code = ?', $row['Code']);

					$dataExist = $this->connectionDefault->fetchOne($selectExist);


					if (empty($dataExist)) {
						$this->connectionDefault->insert(self::WURTH_NAV_SHOPS, $data);
						$this->log .= "Shop has been added in B2B E-shop,Shop code =>>" . $row['Code'] . PHP_EOL;
					}
					if (!empty($dataExist)) {
						$where = ['Code = ?' => (int)$dataExist];

						$this->connectionDefault->update(self::WURTH_NAV_SHOPS, $data, $where);
						$this->log .= "Shop has been updated in B2B E-shop,Shop code =>>" . $row['Code'] . PHP_EOL;
					}
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
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_shops.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

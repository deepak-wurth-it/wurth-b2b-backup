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



/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerGroupImportProcessor
{
	const INDEXER_LIST = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute'];

	const BRANCHES = 'Branches';

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
		LoggerInterface $logger
	) {
		$this->_resourceConnection = $resourceConnection;
		$this->logger = $logger;
		$this->groupFactory = $groupFactory;
		$this->groupRepository = $groupRepository;
		$this->groupFactoryModel = $groupFactoryModel;
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
			$parent = ['A', 'B', 'C', 'D', 'G', 'I', 'M', 'T'];

			$Synchronized='0';
			$select = $this->connectionWurthNav->select()
				->from(
					['branches' => 'Branches']
				)->where('branches.Code IN (?)', $parent)
				->where('branches.Synchronized IN (?)', $Synchronized);



			$data = $this->connectionWurthNav->fetchAll($select);


			if (count($data)) {
				foreach ($data as $row) {
					try {
						$group = $this->groupFactoryModel->create();
						$group->load($row['Name'], 'customer_group_code');

						// If does not exist, set code
						if (!$group->getId()) {
							$group->setCode($row['Name']);
						}
						$group->setCode($row['Name']);
						$group->setData('parent_branch', $row['ParentBranch']);
						$group->setData('branch_code', $row['Code']);
						$group->setTaxClassId(3);
						$group->save(); //save group


						$this->setAndSaveDivision($group->getId(), $row['Code']);

						if ($group->getId()) {
							 $data =  ['Synchronized'=>'1'];	
							 $where = ['Code = ?' => (int)$row['Code']];	
							 $this->connectionWurthNav->update(self::BRANCHES, $data,$where);
							 
							$log = "Done For Row " . $row['Code'] . PHP_EOL;
							echo $log;
							$this->wurthNavLogger($log);
						}

					} catch (\Exception $e) {
						$log = '--------------------- Magento Customer Group Import Error------------------------' . PHP_EOL;
						$log = $e->getMessage() . PHP_EOL;
						echo $log;
						$this->wurthNavLogger($log);
					}
				}
			}
		} catch (\Exception $e) {
			$log = '--------------------- Import Error main method------------------------' . PHP_EOL;
			$log = $e->getMessage() . PHP_EOL;
			echo $log;
			$this->wurthNavLogger($log);
		}
	}

	public function setAndSaveDivision($gid, $wcode)
	{

		$Branches = $this->connectionWurthNav->getTableName('Branches');
		$tableDivision = $this->connectionDefault->getTableName('division');

		//$condition = 'b.Code like ' . '"' . $wcode . '%"';
		$select = $this->connectionWurthNav->select()
			->from(
				['b' => $Branches],
				['*']
			)->where('b.ParentBranch IN (?)', $wcode)
		        ->where("b.Code <> '$wcode' ");

		$data = $this->connectionWurthNav->fetchAll($select);


		if (count($data)) {
			foreach ($data as $row) {

				try {
					$division = [
						'branch_code' => $row['Code'],
						'parent_branch' => $gid,
						'name' => $row['Name'],
						'customer_group_id' => $gid
					];

					if ($id = $this->checkColumnExist($tableDivision, $row['Code'])) {
						$where = ['id IN (?)' => (int)$id];
						$log = "Update division branch_code " . $row['Code'] . PHP_EOL;
						echo $log;
						$this->wurthNavLogger($log);
						$this->connectionDefault->update($tableDivision, $division, $where);
						continue;
					}

					$this->connectionDefault->beginTransaction();
					$this->insertMultiple($tableDivision, $division);
					$log = "Insert division branch_code " . $row['Code'] . PHP_EOL;
					echo $log;
					$this->wurthNavLogger($log);
					$this->connectionDefault->commit();
				} catch (\Exception $e) {
					$log = '---------------------Error Branch Code Import------------------------' . PHP_EOL;
					$log = $e->getMessage() . PHP_EOL;
					echo $log;
					$this->wurthNavLogger($log);
					$this->connectionDefault->rollBack();
				}
			}
		}
	}

	public function insertMultiple($table, $data)
	{
		try {
			$tableName = $this->connectionDefault->getTableName($table);
			return $this->connectionDefault->insertMultiple($tableName, $data);
		} catch (\Exception $e) {
			$log = '---------------------Insert Multiple Error------------------------' . PHP_EOL;
			$log = $e->getMessage() . PHP_EOL;
			echo $log;
			$this->wurthNavLogger($log);
		}
	}


	public function checkColumnExist($table, $key)
	{
		try {
			$tableName = $this->connectionDefault->getTableName($table);
			$select = $this->connectionDefault->select()
				->from(
					['d' => $tableName],
					['*']
				)->where("d.branch_code = '$key'");
			$data = $this->connectionDefault->fetchOne($select);
			return $data;
		} catch (\Exception $e) {
			$log = '---------------------Column Check Error------------------------' . PHP_EOL;
			$log = $e->getMessage() . PHP_EOL;
			echo $log;
			$this->wurthNavLogger($log);
		}
	}

	public function wurthNavLogger($log)
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurth_nav_logger.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

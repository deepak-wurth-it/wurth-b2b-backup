<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Model\ResourceModel\Brand;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	/**
	 * store view id
	 * @var int
	 */
	protected $_storeViewId = null;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	protected $_addedTable = [];

	protected function _construct() {
		$this->_init('Plazathemes\Brandslider\Model\Brand', 'Plazathemes\Brandslider\Model\ResourceModel\Brand');
	}

	/**
	 * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
	 * @param \Magento\Framework\Event\ManagerInterface $eventManager
	 * @param \Zend_Db_Adapter_Abstract $connection
	 * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
	 */
	public function __construct(
		\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
		\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
	) {
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
		$this->_storeManager = $storeManager;

		if ($storeViewId = $this->_storeManager->getStore()->getId()) {
			$this->_storeViewId = $storeViewId;
		}
	}

	/**
	 * get store view id
	 * @return int [description]
	 */
	public function getStoreViewId() {
		return $this->_storeViewId;
	}

	/**
	 * set store view id
	 * @param int $storeViewId [description]
	 */
	public function setStoreViewId($storeViewId) {
		$this->_storeViewId = $storeViewId;
		return $this;
	}

	/**
	 * Multi store view
	 * @param string|array $field
	 * @param null|string|array $condition
	 */
	public function addFieldToFilter($field, $condition = null) {
		$attributes = array(
			'name',
			'status',
			'click_url',
			'image_alt',
			'store_id',
		);
		$storeViewId = $this->getStoreViewId();
		if (in_array($field, $attributes) && $storeViewId) {
			if (!in_array($field, $this->_addedTable)) {
				$this->getSelect();
				$this->_addedTable[] = $field;
			}
			// return parent::addFieldToFilter("IF($field.value IS NULL, main_table.$field, $field.value)", $condition);
			return parent::addFieldToFilter($field, $condition);
		}
		if ($field == 'store_id') {
			$field = 'main_table.brand_id';
		}
		return parent::addFieldToFilter($field, $condition);
	}

	/**
	 * Multi store view
	 */
	protected function _afterLoad() {
		parent::_afterLoad();
		if ($storeViewId = $this->getStoreViewId()) {
			foreach ($this->_items as $item) {
				$item->setStoreViewId($storeViewId)->getStoreViewValue();
			}
		}
		return $this;
	}
	
	/**
     * set order random by brand id
     *
     * @return $this
     */
    public function setOrderByBrand()
    {
        $this->getSelect()->order('order');

        return $this;
    }
}

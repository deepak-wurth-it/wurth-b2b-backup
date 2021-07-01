<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Bannerslider\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	/**
	 * @var \Magento\Backend\Model\UrlInterface
	 */
	protected $_backendUrl;

	protected $_directoryData;

	/**
	 * Store manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * \Magento\Catalog\Model\CategoryFactory
	 * @var [type]
	 */
	protected $_categoryFactory;

	protected $_scopeConfig;

	/**
	 * Country collection
	 *
	 * @var \Magento\Directory\Model\ResourceModel\Country\Collection
	 */
	protected $_countryCollection;

	/**
	 * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
	 */
	protected $_regCollectionFactory;

	/**
	 * [__construct description]
	 * @param \Magento\Framework\App\Helper\Context                      $context              [description]
	 * @param \Magento\Directory\Helper\Data                             $directoryData        [description]
	 * @param \Magento\Directory\Model\ResourceModel\Country\Collection       $countryCollection    [description]
	 * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory [description]
	 * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager         [description]
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Directory\Helper\Data $directoryData,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
		\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory,
		\Magento\Backend\Model\UrlInterface $backendUrl,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		parent::__construct($context);
		$this->_directoryData = $directoryData;
		$this->_countryCollection = $countryCollection;
		$this->_regCollectionFactory = $regCollectionFactory;
		$this->_scopeConfig = $context->getScopeConfig();
		$this->_backendUrl = $backendUrl;
		$this->_storeManager = $storeManager;
		$this->_categoryFactory = $categoryFactory;
	}

	/**
	 * get option country
	 * @return array
	 */
	public function getOptionCountry() {
		$optionCountry = array();
		$countryCollection = $this->_directoryData->getCountryCollection();

		if (count($countryCollection)) {
			foreach ($countryCollection as $country) {
				$optionCountry[] = array('label' => $country->getName(), 'value' => $country->getId());
			}
		}
		return $optionCountry;
	}

	/**
	 * get Base Url Media
	 * @param  string  $path   [description]
	 * @param  boolean $secure [description]
	 * @return string          [description]
	 */
	public function getBaseUrlMedia($path = '', $secure = false) {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, $secure) . $path;
	}

	/**
	 * Get store config
	 *
	 * @param string $path
	 * @param mixed $store
	 * @return mixed
	 */
	public function getConfig($path, $store = null) {
		return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
	}

	public function getSliderBannerUrl() {
		return $this->_backendUrl->getUrl('*/*/banners', ['_current' => true]);
	}

	public function getBackendUrl($route = '', $params = ['_current' => true]) {
		return $this->_backendUrl->getUrl($route, $params);
	}
	
	public function getHtmlIdPrefix() {die('vv');
		return 'page_';
	}
}

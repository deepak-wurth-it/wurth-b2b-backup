<?php
/**
* Copyright © 2016 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\InstagramGallery\Block;

use Magento\Eav\Model\Config;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Plazathemes\InstagramGallery\Block\Cache\Lite;

class InstagramGallery extends \Magento\Framework\View\Element\Template
{
	protected $_config = null;
	protected $_status;
	protected $_resource;
	protected $_eavConfig;
	protected $_visibility;
	protected $_stockHelper;
	protected $_objectManager;
	protected $_categoryCollectionFactory;
	protected $_productsCollectionFactory;
	protected $_scopeConfig;

	public function __construct(
		ResourceConnection $resourceConnection,
		ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\ResourceModel\Category\Collection $collectionFactory,
		Visibility $visibility,
		Stock $stockHelper,
		Config $eavConfig,
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = [],
		$attr = null
	)
	{
		$this->_eavConfig = $eavConfig;
		$this->_visibility = $visibility;
		$this->_stockHelper = $stockHelper;
		$this->_resource = $resourceConnection;
		$this->_objectManager = $objectManager;
		$this->_categoryCollectionFactory = $collectionFactory;
		parent::__construct($context, $data);
	}
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function _helper()
	{
		return $this->_objectManager->get('\Plazathemes\InstagramGallery\Helper\Data');
	}


	public function _getCfg($attr = null)
	{
		// get default config.xml
		$defaults = [];
		$collection = $this->_scopeConfig->getValue('instagramgallery');

		if (empty($collection)) return;
		$groups = [];
		foreach ($collection as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = $this->_scopeConfig->getValue('instagramgallery');
		if (empty($_configs)) return;
		$cfgs = [];

		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfig->getValue('instagramgallery/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			foreach ($_cfgs as $_key => $_cfg) {
				$cfgs[$_key] = $_cfg;
			}
		}

		// get output config
		$configs = [];
		foreach ($defaults as $key => $def) {
			if (isset($defaults[$key])) {
				$configs[$key] = $cfgs[$key];
			} else {
				unset($cfgs[$key]);
			}
		}
		$this->_config = ($attr != null) ? array_merge($configs, $attr) : $configs;
		return $this->_config;
	}

	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

	protected function _toHtml()
	{

		if (!$this->_getConfig('isactive', 1)) return;
		
		$template_file = $this->getTemplate();
		$template_file = (!empty($template_file)) ? $template_file : "Plazathemes_InstagramGallery::plazathemes/instagramgallery/default.phtml";
		$this->setTemplate($template_file);
		
        return parent::_toHtml();
		
	}
}
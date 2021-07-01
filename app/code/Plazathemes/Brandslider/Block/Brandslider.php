<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Block;

class Brandslider extends \Magento\Framework\View\Element\Template {

	protected $_template = 'Plazathemes_Brandslider::brandslider.phtml';

	/**
	 * Brand Factory
	 * @var \Plazathemes\Brandslider\Model\BrandFactory
	 */
	protected $_brandFactory;

	protected $_scopeConfig;


	/**
	 * [__construct description]
	 * @param \Magento\Framework\View\Element\Template\Context                $context                 [description]
	 * @param \Plazathemes\Brandslider\Model\BrandFactory                     $brandFactory           [description]
	 * @param \Magento\Framework\Registry                                     $coreRegistry            [description]
	 * @param \Plazathemes\Brandslider\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory [description]
	 * @param array                                                           $data                    [description]
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Plazathemes\Brandslider\Model\BrandFactory $brandFactory,
		\Plazathemes\Brandslider\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_brandFactory = $brandFactory;
		$this->_brandCollectionFactory = $brandCollectionFactory;
		$this->_scopeConfig = $context->getScopeConfig();
	}
	
	/**
	 * @return
	 */
	public function getBrandSlider() {
		$CurentstoreId = $this->_storeManager->getStore()->getId();
		$sliderCollection = $this->_brandFactory
			->create()
			->getCollection()
			->addFieldToFilter('status', 1)
			->addFieldToFilter('store_id', array('or'=> array(
				0 => array('eq', '0'),
				1 => array('like' => '%'.$CurentstoreId.'%')
				)));
		$sliderCollection->setOrderByBrand();
		return $sliderCollection;
	}
	
	public function getConfig($config)
	{
		return $this->_scopeConfig->getValue('brandslider/general/'.$config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getIdStore()
	{
		return $this->_storeManager->getStore()->getId();
	}
	
	/**
	 * @return
	 */
	public function getMediaFolder() {
		$media_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $media_folder;
	}

	/**
	 * @return
	 */
	protected function _toHtml() {
		$store = $this->_storeManager->getStore()->getId();

		if ($this->_scopeConfig->getValue('brandslider/general/enable_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
			return parent::_toHtml();
		}

		return '';
	}

	/**
	 * Add elements in layout
	 *
	 * @return
	 */
	protected function _prepareLayout() {
		return parent::_prepareLayout();
	}
}

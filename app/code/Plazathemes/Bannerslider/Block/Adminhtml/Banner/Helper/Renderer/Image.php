<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Bannerslider\Block\Adminhtml\Banner\Helper\Renderer;
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
	/**
	 * Store manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	protected $_bannerFactory;

	/**
	 * Registry object
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Plazathemes\Bannerslider\Model\BannerFactory $bannerFactory,
		\Magento\Framework\Registry $coreRegistry,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_storeManager = $storeManager;
		$this->_bannerFactory = $bannerFactory;
		$this->_coreRegistry = $coreRegistry;
	}

	/**
	 * Render action
	 *
	 * @param \Magento\Framework\Object $row
	 * @return string
	 */
	public function render(\Magento\Framework\DataObject $row) {
		$storeViewId = $this->getRequest()->getParam('store');
		$banner = $this->_bannerFactory->create()->setStoreViewId($storeViewId)->load($row->getId());
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $banner->getImage();
		return '<image width="150" height="50" src ="' . $srcImage . '" alt="' . $banner->getImage() . '" >';
	}
}
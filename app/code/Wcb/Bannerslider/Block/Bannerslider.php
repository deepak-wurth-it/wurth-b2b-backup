<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Wcb\Bannerslider\Block;

class Bannerslider extends \Magento\Framework\View\Element\Template {

	protected $_template = 'Wcb_Bannerslider::bannerslider.phtml';

	/**
	 * Banner Factory
	 * @var \Plazathemes\Bannerslider\Model\BannerFactory
	 */
	protected $_bannerFactory;

	protected $_scopeConfig;
	
	/**
	 * Customer group repository
	 *
	 * @var \Magento\Customer\Api\GroupRepositoryInterface
	 */
	protected $groupRepository;
	/**
	 * [__construct description]
	 * @param \Magento\Framework\View\Element\Template\Context                $context                 [description]
	 * @param \Plazathemes\Bannerslider\Model\BannerFactory                     $bannerFactory           [description]
	 * @param \Magento\Framework\Registry                                     $coreRegistry            [description]
	 * @param \Plazathemes\Bannerslider\Model\ResourceModel\Banner\CollectionFactory 
	 * @param \Magento\Customer\Model\Session $customer 
	 * $bannerCollectionFactory [description]
	 * @param array                                                           $data                    [description]
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Plazathemes\Bannerslider\Model\BannerFactory $bannerFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Plazathemes\Bannerslider\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
		\Magento\Customer\Model\Session $customer,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository,		\Magento\Theme\Block\Html\Header\Logo $logo,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_bannerFactory = $bannerFactory;
		$this->_dateTime = $dateTime;
		$this->_bannerCollectionFactory = $bannerCollectionFactory;
		$this->_customer = $customer;
		$this->groupRepository = $groupRepository;
		$this->_logo = $logo;
		$this->_scopeConfig = $context->getScopeConfig();
	}
	
	/**
	 * @return
	 */
	public function getBannerSlider() {
		$CurentstoreId = $this->_storeManager->getStore()->getId();
		$currentDate = $this->_dateTime->gmtDate();
		$sliderCollection = $this->_bannerFactory
			->create()
			->getCollection()
			->addFieldToFilter('status', 1)
			->addFieldToFilter('store_id', array('or'=> array(
				0 => array('eq', '0'),
				1 => array('like' => '%'.$CurentstoreId.'%')
				)))
			->addFieldToFilter(['valid_from', 'valid_to'],[['lteq' => $currentDate], ['gteq' => $currentDate]]);
		$sliderCollection->setOrderByBanner();
		return $sliderCollection;
	}
	public function getCustomerGroup()
	{
		$groupId = $this->_customer->getCustomer()->getGroupId(); //Get customer group Id , you have already this so directly get name
		return $this->getGroupCode($groupId);
	}

	public function getGroupCode($groupId) 
	{
		$group = $this->groupRepository->getById($groupId);
		return $group->getCode();
	}

	public function checkCustomerLoggedIn()
	{
		if($this->_customer->isLoggedIn()) {
			return true;
		 }
		 return false;
	}
	
	public function getEnable()
	{
		return $this->_scopeConfig->getValue('bannerslider/general/enable_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getConfig($config)
	{
		return $this->_scopeConfig->getValue('bannerslider/general/'.$config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

		if ($this->_scopeConfig->getValue('bannerslider/general/enable_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
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

	/**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function isHomePage()
    {	
		return $this->_logo->isHomePage();
	}
}

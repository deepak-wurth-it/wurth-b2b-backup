<?php
namespace Wcb\Catalogslider\Block;

class Catalogslider extends \Magento\Framework\View\Element\Template {
	protected $_template = 'Wcb_Catalogslider::slider.phtml';

	/**
	 * Banner Factory
	 * @var \Plazathemes\Bannerslider\Model\BannerFactory
	 */
	protected $_sliderFactory;

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
	 * @param \Wcb\Catalogslider\Model\CatalogsliderFactory                     $bannerFactory           [description]
	 * @param \Magento\Framework\Registry                                     $coreRegistry            [description]
	 * @param \Wcb\Catalogslider\Model\ResourceModel\Catalogslider\CollectionFactory 
	 * @param \Magento\Customer\Model\Session $customer 
	 * $bannerCollectionFactory [description]
	 * @param array                                                           $data                    [description]
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Wcb\Catalogslider\Model\CatalogsliderFactory $sliderFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Wcb\Catalogslider\Model\ResourceModel\Catalogslider\CollectionFactory $bannerCollectionFactory,
		\Magento\Customer\Model\Session $customer,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Theme\Block\Html\Header\Logo $logo,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_sliderFactory = $sliderFactory;
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
	public function getCatalogsliders() {
		$currentDate = $this->_dateTime->gmtDate();
		$sliderCollection = $this->_sliderFactory
			->create()
			->getCollection()
			->addFieldToFilter('status', 1)
			->addFieldToFilter(['valid_from', 'valid_to'],[['lteq' => $currentDate], ['gteq' => $currentDate]])
            ->setOrder('sort_order', 'ASC');
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
		return $this->_scopeConfig->getValue('wcbcatalog/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getConfig($config)
	{
		return $this->_scopeConfig->getValue('wcbcatalog/general/'.$config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

}

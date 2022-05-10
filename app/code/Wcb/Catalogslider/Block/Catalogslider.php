<?php

namespace Wcb\Catalogslider\Block;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Wcb\Base\Helper\Data;

class Catalogslider extends \Magento\Framework\View\Element\Template
{
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
     * @var Data
     */
    protected $helperData;

    /**
     * [__construct description]
     * @param \Magento\Framework\View\Element\Template\Context $context [description]
     * @param \Wcb\Catalogslider\Model\CatalogsliderFactory $bannerFactory [description]
     * @param \Magento\Framework\Registry $coreRegistry [description]
     * @param \Wcb\Catalogslider\Model\ResourceModel\Catalogslider\CollectionFactory
     * @param \Magento\Customer\Model\Session $customer
     * $bannerCollectionFactory [description]
     * @param array $data [description]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Wcb\Catalogslider\Model\CatalogsliderFactory $sliderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Wcb\Catalogslider\Model\ResourceModel\Catalogslider\CollectionFactory $bannerCollectionFactory,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        Data $helperData,
        Logo $logo,
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
        $this->helperData = $helperData;
    }

    /**
     * @return
     */
    public function getCatalogsliders()
    {
        $currentDate = $this->_dateTime->gmtDate();
        $customerGroupId = $this->helperData->getCustomerGroupId();
        $sliderCollection = $this->_sliderFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(['valid_from', 'valid_to'], [['lteq' => $currentDate], ['gteq' => $currentDate]])
//            ->addFieldToFilter(
//                ['valid_to', 'valid_to'],
//                [['gteq' => $currentDate], ['null' => 'null']]
//            )
//            ->addFieldToFilter(
//                ['valid_from', 'valid_from'],
//                [['lteq' => $currentDate], ['null' => 'null']]
//            )
            ->addFieldToFilter('customer_group', ["finset" => $customerGroupId])
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
        if ($this->_customer->isLoggedIn()) {
            return true;
        }
        return false;
    }

    public function getEnable()
    {
        return $this->_scopeConfig->getValue('wcbcatalog/general/enable', ScopeInterface::SCOPE_STORE);
    }

    public function getConfig($config)
    {
        return $this->_scopeConfig->getValue('wcbcatalog/general/' . $config, ScopeInterface::SCOPE_STORE);
    }

    public function getIdStore()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return
     */
    public function getMediaFolder()
    {
        $media_folder = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $media_folder;
    }
}

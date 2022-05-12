<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.
 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Wcb\Bannerslider\Block;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Plazathemes\Bannerslider\Model\BannerFactory;
use Plazathemes\Bannerslider\Model\ResourceModel\Banner\CollectionFactory;

class Bannerslider extends Template
{
    protected $_template = 'Wcb_Bannerslider::bannerslider.phtml';

    /**
     * Banner Factory
     * @var BannerFactory
     */
    protected $_bannerFactory;

    protected $_scopeConfig;

    /**
     * Customer group repository
     *
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * [__construct description]
     * @param Context $context [description]
     * @param BannerFactory $bannerFactory [description]
     * @param Registry $coreRegistry [description]
     * @param CollectionFactory
     * @param Session $customer
     * $bannerCollectionFactory [description]
     * @param array $data [description]
     */
    public function __construct(
        Context $context,
        BannerFactory $bannerFactory,
        DateTime $dateTime,
        CollectionFactory $bannerCollectionFactory,
        Session $customer,
        GroupRepositoryInterface $groupRepository,
        Logo $logo,
        array $data = []
    )
    {
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
    public function getBannerSlider()
    {
        $CurentstoreId = $this->_storeManager->getStore()->getId();
        $currentDate = $this->_dateTime->gmtDate();
        $sliderCollection = $this->_bannerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('store_id', ['or' => [
                0 => ['eq', '0'],
                1 => ['like' => '%' . $CurentstoreId . '%']
            ]])
        //->addFieldToFilter(['valid_from', 'valid_to'],[['lteq' => $currentDate], ['gteq' => $currentDate]]);
        ->addFieldToFilter(
            ['valid_to', 'valid_to'],
            [['gteq' => $currentDate], ['null' => 'null']]
        )
            ->addFieldToFilter(
                ['valid_from', 'valid_from'],
                [['lteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter('visible_to', ["finset" => $this->getCustomerGroupId()]);

        $sliderCollection->setOrderByBanner();
        return $sliderCollection;
    }

    public function getCustomerGroupId()
    {
        return $groupId = $this->_customer->getCustomerGroupId();
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
        return $this->_scopeConfig->getValue('bannerslider/general/enable_frontend', ScopeInterface::SCOPE_STORE);
    }

    public function getConfig($config)
    {
        return $this->_scopeConfig->getValue('bannerslider/general/' . $config, ScopeInterface::SCOPE_STORE);
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

    /**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function isHomePage()
    {
        return $this->_logo->isHomePage();
    }

    /**
     * @return
     */
    protected function _toHtml()
    {
        $store = $this->_storeManager->getStore()->getId();

        if ($this->_scopeConfig->getValue('bannerslider/general/enable_frontend', ScopeInterface::SCOPE_STORE, $store)) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Add elements in layout
     *
     * @return
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}

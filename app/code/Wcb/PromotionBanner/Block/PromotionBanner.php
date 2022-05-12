<?php

namespace Wcb\PromotionBanner\Block;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Wcb\PromotionBanner\Model\PromotionBannerFactory;
use Wcb\PromotionBanner\Model\ResourceModel\PromotionBanner\CollectionFactory;

class PromotionBanner extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Wcb_PromotionBanner::banners.phtml';

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
     * @param PromotionBannerFactory $bannerFactory [description]
     * @param Registry $coreRegistry [description]
     * @param CollectionFactory
     * @param Session $customer
     * $bannerCollectionFactory [description]
     * @param array $data [description]
     */
    public function __construct(
        Context $context,
        PromotionBannerFactory $bannerFactory,
        DateTime $dateTime,
        CollectionFactory $bannerCollectionFactory,
        Session $customer,
        GroupRepositoryInterface $groupRepository,
        Logo $logo,
        UrlInterface $urlInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_bannerFactory = $bannerFactory;
        $this->_dateTime = $dateTime;
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->_customer = $customer;
        $this->groupRepository = $groupRepository;
        $this->_logo = $logo;
        $this->_urlInterface = $urlInterface;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * Function getAjaxUrl.
     *
     * Used to get the URL to be used in Ajax call.
     *
     * @return string.
     * URL to be used in ajax.
     */
    public function getAjaxUrl()
    {
        return $this->_urlInterface->getUrl('grids/index/view');
    }

    /**
     * @return
     */
    public function getPromotionBanners()
    {
        $currentDate = $this->_dateTime->gmtDate();
        $bannerCollection = $this->_bannerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            //->addFieldToFilter(['valid_from', 'valid_to'], [['lteq' => $currentDate], ['gteq' => $currentDate]])
            ->addFieldToFilter(
                ['valid_to', 'valid_to'],
                [['gteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter(
                ['valid_from', 'valid_from'],
                [['lteq' => $currentDate], ['null' => 'null']]
            )
            ->addFieldToFilter('customer_group', ["finset" => $this->getCustomerGroupId()])
            ->setOrder('sort_order', 'ASC');
        return $bannerCollection;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    public function getCustomerGroup()
    {
        $groupId = $this->_customer->getCustomer()->getGroupId(); //Get customer group Id , you have already this so directly get name
        return $this->getGroupCode($groupId);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        //$groupId = $this->_customer->getCustomer()->getGroupId(); //Get customer group Id
        return $groupId = $this->_customer->getCustomerGroupId(); //Get customer group Id
    }

    /**
     * @param $groupId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroupCode($groupId)
    {
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }

    /**
     * @return bool
     */
    public function checkCustomerLoggedIn()
    {
        if ($this->_customer->isLoggedIn()) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->_scopeConfig->getValue('wcbbanner/general/enable', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getConfig($config)
    {
        return $this->_scopeConfig->getValue('wcbbanner/general/' . $config, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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

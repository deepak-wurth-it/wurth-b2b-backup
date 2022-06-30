<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Sales\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Sales\Block\Order\History
{

    /**
     * @var CollectionFactoryInterface
     */


    private $orderCollectionFactory;

    /**
     * @var string
     */
    protected $_template = 'Wcb_Sales::order/history.phtml';



    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->context =  $context;
        //print_r(get_class_methods($this->context));exit;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }


    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;

        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        $this->orders->setPageSize($pageSize);
        $this->orders->setCurPage($page);
        return $this->orders;
    }

    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }


    public function getTable(string $name)
    {
        return $this->resource->getTableName($name);
    }
    public function LoadCustomerById($customerId)
    {
        $customer = $this->customerFactory->create();
        $cst = $customer->load($customerId);
        return $cst;
    }


    public function getAvailableLimit()
    {
        return [5 => 5, 50 => 50, 100 => 100, 150 => 150, 200 => 200];
    }

    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('pager');
       
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());
            $pagerBlock->setShowPerPage(true);
            $pagerBlock->setCollection($this->getOrders());
            return $pagerBlock->toHtml();
        }

        return '';
    }

    public function _prepareLayout()
    { 
        $breadcrumbsBlock = $this->getLayout()->getBlock('wcb_breadcrumb');
        $baseUrl = $this->context->getStoreManager()->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'online_shop',
                [
                'label' => __('Online Shop'), //lable on breadCrumbes
                'title' => __('Online Shop'),
                'link' => $baseUrl
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'tracking_order',
                [
                'label' => __('Tracking Order'),
                'title' => __('Tracking Order'),
                'link' => '/sales/order/history/'
                ]
            );
        }
        $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
    }
}

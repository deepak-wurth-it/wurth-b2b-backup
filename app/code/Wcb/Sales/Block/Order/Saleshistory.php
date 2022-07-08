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
class Saleshistory extends \Magento\Sales\Block\Order\History
{

    /**
     * @var CollectionFactoryInterface
     */


    private $orderCollectionFactory;

    /**
     * @var string
     */
    protected $_template = 'Wcb_Sales::order/sales-history.phtml';



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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->_productRepository = $productRepository;
        $this->imageHelperFactory = $imageHelperFactory;

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

        return $this->orders;
    }


    public function getPartialShippedOrderItem()
    {
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



    public function getProduct($pid)
    {

        $product = $this->_productRepository->getById($pid);
        return  $product;
    }
    public function getProductThumbUrl($product)
    {


        $thumbUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_thumbnail_image')->getUrl();
        //$thumb = $product->getData('thumbnail');
        return $thumbUrl;
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
                    'label' => __('Undelivered Item'),
                    'title' => __('Undelivered Item'),
                    'link' => '/wcbsales/order/undelivered/'
                ]
            );
        }
        $this->pageConfig->getTitle()->set(__('Undelivered Item')); // set page name
        return parent::_prepareLayout();
    }
}

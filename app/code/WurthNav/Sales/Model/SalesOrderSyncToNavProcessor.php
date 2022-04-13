<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrdersFactory as OrdersFactory;
use \WurthNav\Sales\Model\OrdersItemsFactory as OrdersItemsFactory;

/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesOrderSyncToNavProcessor
{

    protected $salesShipment;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		OrdersFactory $ordersFactory,
        OrdersItemsFactory $ordersItemsFactory,
        LoggerInterface $logger
    ) {

        $this->storeManager = $storeManager;
        $this->ordersFactory = $ordersFactory;
        $this->ordersItemsFactory = $ordersItemsFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;

    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install()
    {
       $orders = $this->getAllOrder();
       if($orders->getSize() > 0 && $orders->count() > 0){
		   foreach($orders as $order){
					 $ordersNav = $this->ordersFactory->create();
					 $orderId = $order->getId();
					 $ordersNavExist = $this->loadByOrderId($ordersNav,$orderId);
					 if($ordersNavExist){
					   $ordersNav = $ordersNavExist;
					 }
					 $method = $order->getPayment()->getMethodInstance();
					 $methodTitle = $method->getTitle();
					 $name = $order->getCustomerFirstname().' '. $order->getCustomerLastname();
					 $ordersNav->setData('OrderID',$order->getId());
					 $ordersNav->setData('CreatedBy',$name);
					 //$ordersNav ->setData('CustomerCode',$order->getId()); // will update
					 // $ordersNav ->setData('TotalNoTax',$order->getId());//Order total without Tax	
					 //$ordersNav ->setData('Tax',$order->getId());//Tax amount of the order
					 //$ordersNav ->setData('Total',$order->getGrandTotal());//Total with tax
					 $ordersNav->setData('CreatedDate',$order->getCreatedAt());//Order Created Date
					 $ordersNav->setData('OrderStatus',$order->getOrderStatus());//Order Status
					 $ordersNav->setData('PaymentType',$methodTitle);//Order Payment Method
					 $ordersNav->setData('LastUpdate',$order->getUpdatedAt());//Order Last Update
					 $ordersNav->save(); // New Save

		   }
		   
	   }

    }
    
    public function getAllOrder()
    {
        $orders = $this->orderCollectionFactory->create();
        return $orders->addFieldToFilter('order_sync_status_nav',  array('eq' => NULL ));

    }
    
    public function loadByOrderId($ordersNav,$orderId){
		
		return $ordersNav->load($orderId,'OrderID');
	
	}
}

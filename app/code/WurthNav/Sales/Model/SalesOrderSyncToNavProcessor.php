<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrdersFactory;
use \WurthNav\Sales\Model\OrderItemsFactory;


class SalesOrderSyncToNavProcessor
{

	public $log;
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		OrdersFactory $ordersFactory,
		OrderItemsFactory $orderItemsFactory,
		LoggerInterface $logger
	) {

		$this->storeManager = $storeManager;
		$this->ordersFactory = $ordersFactory;
		$this->orderItemsFactory = $orderItemsFactory;
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->logger = $logger;
	}

	public function install()
	{
		$orders = $this->getAllOrder();

		if ($orders->getSize() > 0 && $orders->count() > 0) {
			foreach ($orders as $order) {
				try {

					$ordersNav = $this->ordersFactory->create();
					$orderId = $order->getId();

					$ordersNavExist = $this->loadByOrderId($ordersNav, $orderId);
					if($ordersNavExist == "no_update"){
						continue;
					}

					if ($ordersNavExist && $ordersNavExist != "no_update") {
						$ordersNav = $ordersNavExist;
						$this->log = 'Updated order Id  =>>' . $orderId . PHP_EOL;
					} else {
						$this->log = 'Imported order Id  =>>' . $orderId . PHP_EOL;
					}

					$method = $order->getPayment()->getMethodInstance();
					$methodTitle = $method->getTitle();
					$name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
					$ordersNav->setData('OrderID', $order->getId());

					$ordersNav->setData('CreatedBy', $name);
					$ordersNav->setData('CustomerCode', $order->getCustomerCode()); // will update
					$ordersNav->setData('DeliveryAddressCode', $order->getDeliveryAddressCode()); // will update	 

					$ordersNav->setData('CustomerOrderNo', $order->getCustomerOrderNo()); // will update
					$ordersNav->setData('CostCenter', $order->getCostCenter()); // will update
					$ordersNav->setData('LocationCode', $order->getLocationCode()); // will update
					$ordersNav->setData('TotalNoTax', $order->getSubtotal()); //Order total without Tax	


					$ordersNav->setData('Tax', $order->getTaxAmount()); //Tax amount of the order
					$ordersNav->setData('Total', $order->getGrandTotal()); //Total with tax
					$ordersNav->setData('CreatedDate', $order->getCreatedAt()); //Order Created Date
					$ordersNav->setData('OrderStatus', $order->getOrderStatus()); //Order Status
					$ordersNav->setData('PaymentType', $methodTitle); //Order Payment Method
					$ordersNav->setData('LastUpdate', $order->getUpdatedAt()); //Order Last Update
					$ordersNav->setData('NeedsUpdate', '1'); // Needs Update
					$ordersNav->setData('Synchronized', '1'); // Needs Update
					$ordersNav->save(); // New Save

					$this->SaveOrderItems($order);
					
				} catch (\Exception $e) {
					$this->logger->info($e->getMessage());
					echo $e->getMessage() . PHP_EOL;
					continue;
				}
			}
		}
	}


	public function getAllOrder()
	{
		$orders = $this->orderCollectionFactory->create();
		return $orders->addFieldToFilter('order_sync_status_nav',  array('eq' => NULL));
	}

	public function loadByOrderId($ordersNav, $orderId)
	{
		$order = $ordersNav->load($orderId, 'OrderID');
	
		if($order->getData('Synchronized') && $order->getData('NeedsUpdate')){
			
			$order = "no_update";
		}
		return $order;
	}

	public function loadByItemId($orderItems, $orderItemId)
	{

		return $orderItems->load($orderItemId, 'OrderItemID');
	}


	public function SaveOrderItems($order)
	{

		$orderItems = $order->getAllItems();
		foreach ($orderItems as $items) {
			try {
				$orderItemsNav = $this->orderItemsFactory->create();
				$orderItemId = $items->getId();
				$orderItemNavExist = $this->loadByItemId($orderItemsNav, $orderItemId);
				if ($orderItemNavExist) {
					$orderItemsNav = $orderItemNavExist;
					$this->log .= 'Updated order items Id =>>' .  $orderItemId . PHP_EOL;
				} else {
					$this->log .= 'Imported order items Id =>>' .  $orderItemId . PHP_EOL;
				}

				$orderItemsNav->setData('OrderItemID', $items->getId());
				$orderItemsNav->setData('OrderID', $items->getOrderId());
				$orderItemsNav->setData('ProductNumber', $items->getSku()); // Product number of the item
				$orderItemsNav->setData('LocationCode', $order->getLocationCode()); // will update
				$orderItemsNav->setData('TotalNoTax', $items->getRowTotal()); //Order total without Tax	
				$orderItemsNav->setData('Quantity', $items->getQtyOrdered()); // Number of quantity of the item
				$orderItemsNav->setData('Price', $items->getPrice()); //Price per unit for the respective item
				$orderItemsNav->setData('Discount', $items->getDiscountAmount()); // Discount for the respective item
				$orderItemsNav->setData('CreatedDate', $items->getCreatedAt()); //Order Created Date
				$orderItemsNav->setData('LastUpdate', $items->getUpdatedAt()); //Order Last Update
				$orderItemsNav->save(); // New Save

			} catch (\Exception $e) {
				$this->logger->info($e->getMessage());
				echo $e->getMessage() . PHP_EOL;
				continue;
			}
		}


		$this->wurthNavLogger($this->log);
	}


	public function wurthNavLogger($log = null)
	{
		echo $log;
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_order_import.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($log);
	}
}

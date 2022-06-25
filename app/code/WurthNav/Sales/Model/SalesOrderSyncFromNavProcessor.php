<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrdersFactory;
use \WurthNav\Sales\Model\OrderItemsFactory;


class SalesOrderSyncFromNavProcessor
{

	public $log;
	const SHIPPING_DETAILS = 'ShippingDetails';
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		OrdersFactory $ordersFactory,
		OrderItemsFactory $orderItemsFactory,
		LoggerInterface $logger
	) {

		$this->storeManager = $storeManager;
		$this->ordersFactory = $ordersFactory;
		$this->orderItemsFactory = $orderItemsFactory;
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->logger = $logger;
		$this->orderRepository = $orderRepository;
		$this->_resourceConnection = $resourceConnection;
		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();
	}

	public function startProcess()
	{
		$orders = $this->getAllOrder();

		if ($orders->getSize() > 0 && $orders->count() > 0) {
			foreach ($orders as $order) {
				//try {
					$magentoOrderId = $order['OrderID'];
					$orderObject = $this->getOrder($magentoOrderId);
					$orderObject->setData('wcb_invoice_no', $order->getInvoiceNo()); 
					$orderObject->setData('wcb_tracking_link', $order->getTrackingLink()); 
					$orderObject->setData('wcb_external_id', $order->getExternalID()); 
					$orderObject->setData('wcb_order_status', $order->getOrderStatus());
					$orderObject->setData('wcb_delivery_status_code', $order->getDeliveryStatusCode()); 
					$orderObject->setData('wcb_delivery_status_desc', $order->getDeliveryStatusDesc()); 
					$orderObject->save();
					$this->SaveOrderItems($magentoOrderId);
				// } catch (\Exception $e) {
				// 	$this->logger->info($e->getMessage());
				// 	echo $e->getMessage() . PHP_EOL;
				// 	continue;
				// }
			}
		}
	}



	public function getOrder($id)
	{
		return $this->orderRepository->get($id);
	}

	public function getAllOrder()
	{
		$orders = $this->ordersFactory->create()->getCollection();
		
		return $orders->addFieldToFilter('Synchronized',  array('eq' => '0'));
	}

	public function loadByOrderId($ordersNav, $orderId)
	{
		$order = $ordersNav->load($orderId, 'OrderID');

		if ($order->getData('Synchronized') && $order->getData('NeedsUpdate')) {

			$order = "no_update";
		}
		return $order;
	}

	public function loadByItemId($orderItems, $orderItemId)
	{

		return $orderItems->load($orderItemId, 'OrderItemID');
	}


	public function getOrderItems($orderId){
		echo 'fsdfsdf';exit;
		$orderItemsNav = $this->orderItemsFactory->create();
		$orderItemsNav->getCollection()->addFieldToFilter('OrderID',$orderId);
		echo $orderItemsNav->getSelect();exit;
		return $orderItemsNav;

	}

	public function SaveOrderItems($orderId)
	{

		$orderItems = $order->getOrderItems($orderId);
		print_r($orderItems->getData());exit;
		$i = 1;
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

				$orderItemsNav->setData('MagentoOrderItemId', $items->getId()); //
				$orderItemsNav->setData('OrderItemID', $i); //Loop Id
				$orderItemsNav->setData('ProductNumber', $items->getId()); //Product Code
				$orderItemsNav->setData('OrderID', $items->getOrderId());
				$orderItemsNav->setData('LocationCode', $order->getLocationCode()); // will update
				$orderItemsNav->setData('TotalNoTax', $items->getRowTotal()); //It's subtotal (without tax) of purchased qty of an item
				//$orderItemsNav->setData('Quantity', $items->getQtyOrdered()); // Number of quantity of the item/Confusion
				$orderItemsNav->setData('Discount', $items->getDiscountAmount()); // Discount for the respective item
				//Other
				$orderItemsNav->setData('Price', $items->getWcbPrice()); // Origional price
				$orderItemsNav->setData('Discount', $items->getWcbDiscountPrice()); // only discount
				$orderItemsNav->setData('Packaging', $items->getWcbQuantityOrdered()); // only discount
				$orderItemsNav->setData('Quantity', $items->getWcbOrderUnit()); // only discount
				$orderItemsNav->setData('Promised Delivery Date', $items->getPromisedDeliveryDate()); // Discount for the respective item


				$orderItemsNav->setData('CreatedDate', $items->getCreatedAt()); //Order Created Date
				$orderItemsNav->setData('LastUpdate', $items->getUpdatedAt()); //Order Last Update
				$orderItemsNav->save(); // New Save
				$i++;
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

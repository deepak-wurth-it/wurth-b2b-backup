<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrdersFactory;
use \WurthNav\Sales\Model\OrderItemsFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;


class SalesOrderSyncFromNavProcessor
{

	public $log;
	const ERP_ORDERS = 'Orders';
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		OrdersFactory $ordersFactory,
		OrderItemsFactory $orderItemsFactory,
		OrderItemRepositoryInterface $orderItemRepository,
		LoggerInterface $logger
	) {

		$this->storeManager = $storeManager;
		$this->ordersFactory = $ordersFactory;
		$this->orderItemsFactory = $orderItemsFactory;
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->logger = $logger;
		$this->orderRepository = $orderRepository;
		$this->_resourceConnection = $resourceConnection;
		$this->orderItemRepository = $orderItemRepository;
		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();
	}

	public function startProcess()
	{
		$orders = $this->getAllOrder();
		if ($orders->getSize() > 0 && $orders->count() > 0) {
			foreach ($orders as $order) {
				try {
				$magentoOrderId = $order->getData('OrderID');
				$orderObject = $this->getOrder($magentoOrderId);
				$orderObject->setData('wcb_invoice_no', $order->getInvoiceNo());
				$orderObject->setData('wcb_tracking_link', $order->getTrackingLink());
				$orderObject->setData('wcb_external_id', $order->getExternalID());
				$orderObject->setData('wcb_order_status', $order->getOrderStatus());
				$orderObject->setData('wcb_delivery_status_code', $order->getDeliveryStatusCode());
				$orderObject->setData('wcb_delivery_status_desc', $order->getDeliveryStatusDesc());
				$orderObject->save();
				$order->setData('Synchronized','1');
				$order->save();
				
				$this->log .= 'Two Way Sync ===>> Update magento order id   =>>' . $magentoOrderId . PHP_EOL;
				$this->SaveOrderItems($magentoOrderId);
				$this->wurthNavLogger($this->log);

				} catch (\Exception $e) {
					$this->logger->info($e->getMessage());
					echo $e->getMessage() . PHP_EOL;
					continue;
				}
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



	public function getOrderItems($orderId)
	{
		$orderItemsNav = $this->orderItemsFactory->create();
		$orderItemsNav = $orderItemsNav->getCollection()->addFieldToFilter('OrderID', $orderId);

		return $orderItemsNav;
	}

	public function SaveOrderItems($orderId)
	{

		$orderItems = $this->getOrderItems($orderId);
		$keyField = 'MagentoOrderItemId';
		$i = 1;
		foreach ($orderItems as $items) {
			try {
				$itemId = $items->getData($keyField);
				$itemCollection = $this->getOrderItem($itemId);
				$itemCollection->setData('wcb_item_status', $items->getData('Status'));
				$itemCollection->setData('wcb_shipped_quantity', $items->getData('ShippedQuantity'));
				$itemCollection->setData('wcb_completely_shipped', $items->getData('CompletelyShipped'));
				$itemCollection->setData('wcb_promised_delivery_date', $items->getData('Promised Delivery Date'));
				$itemCollection->save();
				$this->log .= 'Two Way Sync ===>> Update magento order item id   =>>' . $itemId . PHP_EOL;

			} catch (\Exception $e) {
				$this->logger->info($e->getMessage());
				echo $e->getMessage() . PHP_EOL;
				continue;
			}
		}
	}

	public function getOrderItem($itemId): OrderItemInterface
	{
		return $this->orderItemRepository->get($itemId);
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

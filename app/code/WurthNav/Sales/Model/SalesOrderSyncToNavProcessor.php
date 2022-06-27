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
	const SHIPPING_DETAILS = 'ShippingDetails';
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		OrdersFactory $ordersFactory,
		OrderItemsFactory $orderItemsFactory,
		LoggerInterface $logger
	) {

		$this->storeManager = $storeManager;
		$this->ordersFactory = $ordersFactory;
		$this->orderItemsFactory = $orderItemsFactory;
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->logger = $logger;
		$this->_resourceConnection = $resourceConnection;
		$this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
		$this->connectionDefault  = $this->_resourceConnection->getConnection();
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

					if ($ordersNavExist == "no_update") {
						continue;
					}

					if ($ordersNavExist && $ordersNavExist != "no_update") {
						$ordersNav = $ordersNavExist;
						$this->log .= 'Updated order Id  =>>' . $orderId . PHP_EOL;
					} else {
						$this->log .= 'Imported order Id  =>>' . $orderId . PHP_EOL;
					}


					$method = $order->getPayment()->getMethodInstance();
					$methodTitle = $method->getTitle();
					$name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
					$ordersNav->setData('OrderID', $order->getId());

					$ordersNav->setData('CreatedBy', $name);
					$ordersNav->setData('CustomerCode', $order->getCustomerCode()); // will update
					$ordersNav->setData('DeliveryAddressCode', $order->getDeliveryAddressCode()); // will update from delivery address 	 

					$ordersNav->setData('CustomerOrderNo', $order->getInternalOrderNumber()); // will update/Internal Order number
					$ordersNav->setData('CostCenter', $order->getCostCenter()); // will update// Dimension value
					$ordersNav->setData('LocationCode', $order->getLocationCode()); // will update // Shop Contact
					$ordersNav->setData('TotalNoTax', $order->getSubtotal()); //Order total without Tax	


					$ordersNav->setData('Tax', $order->getTaxAmount()); //Tax amount of the order
					$ordersNav->setData('Total', $order->getGrandTotal()); //Total with tax
					$ordersNav->setData('CreatedDate', $order->getCreatedAt()); //Order Created Date
					$ordersNav->setData('OrderStatus', $order->getOrderStatus()); //Order Status/This should to be 1 after order palce
					$ordersNav->setData('PaymentType', $methodTitle); //Order Payment Method
					$ordersNav->setData('Comment', $order->getRemarks()); //Order Comment
					//Other Field

					$ordersNav->setData('ExternalId', $order->getExternalId()); //ERP Order Id
					$ordersNav->setData('DeliveryAddressCode', $order->getExternalId()); //it displays delivery address code Linkage with dboCustomerDeliveryAddress
					$ordersNav->setData('CustomerOrderNo', $order->getInternalOrderNumber()); //User enters Internal order number during checkout
					$ordersNav->setData('CostCenter', $order->getCostCenter()); //cost center
					$ordersNav->setData('LocationCode', $order->getLocationCode()); //Order is placed for Delivery or Store Number

					$ordersNav->setData('LastUpdate', $order->getUpdatedAt()); //Order Last Update
					$ordersNav->setData('NeedsUpdate', '1'); // Needs Update
					$ordersNav->setData('Synchronized', '1'); // Needs Update
					$ordersNav->save(); // New Save



					$this->SaveShippingAddress($order);
					$this->SaveOrderItems($order);
				} catch (\Exception $e) {
					$this->logger->info($e->getMessage());
					echo $e->getMessage() . PHP_EOL;
					continue;
				}
			}
		}
	}


	public function SaveShippingAddress($order)
	{
		$data = "";
		$row = $shippingAddress = $order->getShippingAddress()->getData();
		if (count($shippingAddress)) {
			try {
				
				$data = [
					'OrderID' => $order->getId(),
					'Name' =>  $row['firstname'] . ' ' . $row['lastname'],
					'Street' => $row['street'],
					'Country' => $row['country_id'],
					'PostalCode' => $row['postcode'],
					'City' => $row['city'],
					'Phone' => $row['telephone'],
					'IsWholesale' => '1',
					'FullDelivery' => $order->getDeliveryOrder()
				];



				$selectExist = $this->connectionWurthNav->select()
					->from(
						['shp' => self::SHIPPING_DETAILS]
					)
					->where('OrderID = ?', $order->getId());

				$dataExist = $this->connectionWurthNav->fetchOne($selectExist);

				if (empty($dataExist)) {
					$this->connectionWurthNav->insert(self::SHIPPING_DETAILS, $data);
					$this->log .= "Shipping Address Has Been Added for Order Id =>>" . $order->getId() . PHP_EOL;
				}
				if (!empty($dataExist)) {
					$where = ['OrderID = ?' => (int)$dataExist];

					$this->connectionWurthNav->update(self::SHIPPING_DETAILS, $data, $where);
					$this->log .= "Shipping Address Has Been Added for Order Id =>>" . $order->getId() . PHP_EOL;
				}
			} catch (\Exception $e) {
				$this->logger->info($e->getMessage());
				echo $e->getMessage() . PHP_EOL;
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

		if ($order->getData('Synchronized') && $order->getData('NeedsUpdate')) {

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
		$i=1;
		foreach ($orderItems as $key=>$items) {
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

				$orderItemsNav->setData('MagentoOrderItemId', $items->getId());//
				$orderItemsNav->setData('OrderItemID', $i);//Loop Id
				$orderItemsNav->setData('ProductNumber', $items->getId());//Product Code
				$orderItemsNav->setData('OrderID', $items->getOrderId());
				$orderItemsNav->setData('LocationCode', $order->getLocationCode()); // will update
				$orderItemsNav->setData('TotalNoTax', $items->getRowTotal()); //It's subtotal (without tax) of purchased qty of an item
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

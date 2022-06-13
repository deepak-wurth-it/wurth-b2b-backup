<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrdersFactory;
use \WurthNav\Sales\Model\OrderItemsFactory;

class SalesOrderOrderStatusProcessor
{
	const PREPARING_FOR_SHIPMENT= 'preparing_for_shipment';
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order $magentoOrder,
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
        $this->orderRepository = $orderRepository;
        $this->magentoOrder = $magentoOrder;
        $this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
        $this->connectionDefault = $this->_resourceConnection->getConnection();
    }





    public function install()
    {
        $select = $this->connectionWurthNav->select()->from(['order' => 'OrderStatus']);
        $data = $this->connectionWurthNav->fetchAll($select);

        if (!empty($data)) {
            foreach ($data as $order) {
                $status = $order->getData('Name');
                $ID = $order->getData('ID');
                $status = $name = str_replace(' ', '_', $status);
                $status = strtolower($name);
                $order = $this->orderRepository->get($$ID);

                if ($order->getId() && $status) {
                    $state = $order->getState();
                    if ($state == $status) {
                        continue;
                    }
                    if ($status === 'processing') {
                        $orderState = Order::STATE_PROCESSING;
                        $order->setState($orderState)->setStatus($orderState);
                        $order->save();
                    }
                    if ($status === 'preparing_for_shipment') {
                        $orderState = Self::PREPARING_FOR_SHIPMENT;
                        $order->setState($orderState)->setStatus($orderState);
                        $order->save();
                    }
                    if ($status === 'shipped_completed') {
                        $orderState = Order::STATE_COMPLETE;
                        $order->setState($orderState)->setStatus($orderState);
                        $order->save();
                    }
                    if ($status === 'cancelled') {
                        $orderState = Order::STATE_CANCELED;
                        $order->setState($orderState)->setStatus($orderState);
                        $order->save();
                    }
                }
            }
        }
    }
}

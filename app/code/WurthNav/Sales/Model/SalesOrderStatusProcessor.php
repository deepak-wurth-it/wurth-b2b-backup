<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WurthNav\Sales\Model;

use Psr\Log\LoggerInterface;
use \WurthNav\Sales\Model\OrderItemsFactory;

class SalesOrderStatusProcessor
{
    const PREPARING_FOR_SHIPMENT = 'preparing_for_shipment';
    public $log;
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger

    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;

        $this->_resourceConnection = $resourceConnection;
        $this->connectionWurthNav = $this->_resourceConnection->getConnection('wurthnav');
        $this->connectionDefault = $this->_resourceConnection->getConnection();
    }





    public function startProcess()
    {
        $select = $this->connectionWurthNav->select()->from(['order' => 'OrderStatus']);
        $data = $this->connectionWurthNav->fetchAll($select);

        if (!empty($data)) {
            foreach ($data as $order) {
                try {
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

                    $this->log .= "Order Status has been changed for order ID " . $ID . PHP_EOL;
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    echo $e->getMessage() . PHP_EOL;
                    continue;
                }
            }
        }
        $this->wurthNavLogger($this->log);
        // No Synchronized or need_update  Other validation field found for previous  done lines

    }

    public function wurthNavLogger($log = null)
    {
        echo $log;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wurthnav_order_status_change.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
}

<?php

namespace Wurth\Shippingproduct\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Wcb\Checkout\Helper\Data;

class AddCartProductAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Data $data,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->data = $data;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getData('quote_item');
            $product = $observer->getEvent()->getData('product');
            $quantityUnitByQuantity = $this->data->getQuantityUnitByQuantity($item->getQty(), $product);
            $wcbMinUnitQuantity = $this->data->getMinimumAndMeasureQty($product);
            $item->setWcbQuantityOrdered($wcbMinUnitQuantity);
            $item->setWcbOrderUnit($quantityUnitByQuantity);
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}

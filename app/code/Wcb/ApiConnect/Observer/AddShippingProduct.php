<?php

namespace Wcb\ApiConnect\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Wurth\Shippingproduct\Helper\AddRemoveShippingProduct;

class AddShippingProduct implements ObserverInterface
{
    /**
     * @var AddRemoveShippingProduct
     */
    protected $addRemoveShippingProduct;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AddShippingProduct constructor.
     * @param AddRemoveShippingProduct $addRemoveShippingProduct
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddRemoveShippingProduct $addRemoveShippingProduct,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->addRemoveShippingProduct = $addRemoveShippingProduct;
    }

    /**
     * Add shipping product using event
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $cart = $observer->getData('cart');
            $quote = $observer->getEvent()->getItem()->getQuote();
            $this->addRemoveShippingProduct->updateShippingProduct($quote, $api = 1);
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}

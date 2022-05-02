<?php

namespace Wurth\Theme\Observer;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

class SaveOrderDetail implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Session
     */
    protected $quoteFactory;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     */

    public function __construct(
        LoggerInterface $logger,
        QuoteFactory $quoteFactory
    ) {
        $this->_logger = $logger;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $order->setOrderConfirmationEmail($quote->getOrderConfirmationEmail());
            $order->setInternalOrderNumber($quote->getInternalOrderNumber());
            $order->setRemarks($quote->getRemarks());
            $order->setDeliveryOrder($quote->getDeliveryOrder());
            $order->save();
        } catch (Exception $e) {
        }
    }
}

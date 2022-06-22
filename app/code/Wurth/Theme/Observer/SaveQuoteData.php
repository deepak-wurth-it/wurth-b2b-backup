<?php

namespace Wurth\Theme\Observer;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

class SaveQuoteData implements ObserverInterface
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
     * @var RequestInterface
     */
    private $request;

    /**
     * SaveQuoteData constructor.
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     * @param RequestInterface $request
     */
    public function __construct(
        LoggerInterface $logger,
        QuoteFactory $quoteFactory,
        RequestInterface $request
    ) {
        $this->_logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $postData = file_get_contents("php://input");
        if ($postData) {
            $postData = json_decode($postData, true);
        }
        if (isset($postData['additional_properties'])) {
            try {
                $order = $observer->getOrder();
                //$quoteId = $order->getQuoteId();
                $quote = $this->quoteFactory->create()->load($postData['additional_properties']['quote_id']);
                $quote->setOrderConfirmationEmail($postData['additional_properties']['order_confirmation_email']);
                $quote->setInternalOrderNumber($postData['additional_properties']['internal_order_number']);
                $quote->setRemarks($postData['additional_properties']['remarks']);
                $quote->setDeliveryOrder(0);
                $quote->save();
            } catch (Exception $e) {
            }
        }
    }
}

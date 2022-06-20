<?php

namespace Wurth\Theme\Observer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;

class SaveOrderDetail implements ObserverInterface
{

    /**
     * @var Session
     */
    protected $quoteFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     */

    public function __construct(
        QuoteFactory $quoteFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
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

            // set customer code
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customerCode = '';
            if ($customer->getCustomAttribute('customer_code')) {
                $customerCode = $customer->getCustomAttribute('customer_code')->getValue();
            }

            $order->setOrderConfirmationEmail($quote->getOrderConfirmationEmail());
            $order->setInternalOrderNumber($quote->getInternalOrderNumber());
            $order->setRemarks($quote->getRemarks());
            $order->setDeliveryOrder($quote->getDeliveryOrder());
            $order->setCustomerCode($customerCode);
            $order->save();
        } catch (Exception $e) {
        }
    }
}

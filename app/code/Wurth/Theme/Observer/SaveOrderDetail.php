<?php

namespace Wurth\Theme\Observer;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Wcb\Checkout\Helper\Data as CheckoutHelper;

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
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * Constructor
     *
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     */

    public function __construct(
        QuoteFactory $quoteFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->addressRepository = $addressRepository;
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
            $order->setDeliveryAddressCode($this->getAddressCode($order));
            $order->setCostCenter($this->getCostCenterCode($quote));
            $order->setLocationCode($this->getLocationCode($quote));
            $order->save();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $order
     * @return mixed|null
     */
    public function getAddressCode($order)
    {
        $addressCode = null;
        $billingAddressId = $order->getBillingAddress()->getData('customer_address_id');
        $shippingAddressId = $order->getShippingAddress()->getData('customer_address_id');

        if ($billingAddressId && $shippingAddressId) {
            if ($billingAddressId != $shippingAddressId) {
                $addressInfo = $this->getAddressData($shippingAddressId);
                if ($addressInfo->getCustomAttribute('address_code')) {
                    $addressCode = $addressInfo->getCustomAttribute('address_code')->getValue();
                }
            }
        }

        return $addressCode;
    }

    /**
     * @param $addressId
     * @return array|AddressInterface
     */
    public function getAddressData($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param $quote
     * @return string
     */
    public function getCostCenterCode($quote)
    {
        if ($quote->getData('pickup_store_id')) {
            return checkoutHelper::WEB_CLICK_COLLECT_CODE;
        } else {
            return checkoutHelper::WEB_DELIVERY_CODE;
        }
    }
    public function getLocationCode($quote)
    {
        if ($quote->getData('pickup_store_id')) {
            return null;// Null value replace in feature while select click and collect
        } else {
            return checkoutHelper::WEB_DELIVERY_LOCATION_CODE;
        }
    }
}

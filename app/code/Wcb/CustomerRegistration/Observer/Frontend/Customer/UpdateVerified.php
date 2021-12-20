<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Observer\Frontend\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;

class UpdateVerified implements \Magento\Framework\Event\ObserverInterface
{
 
    protected $customerRepository;
 
    public function __construct(
 
        CustomerRepositoryInterface $customerRepository)
 
    {
 
        $this->customerRepository = $customerRepository;
 
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
 
    {

        $customer = $observer->getEvent()->getCustomer();
 
        $customer->setCustomAttribute('verified', 0);
 
        $this->customerRepository->save($customer);

        return $this;
 
    }
 
}


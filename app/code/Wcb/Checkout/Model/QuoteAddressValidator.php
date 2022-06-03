<?php

namespace Wcb\Checkout\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

class QuoteAddressValidator extends \Magento\Quote\Model\QuoteAddressValidator
{
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession
    ) {
        parent::__construct($addressRepository, $customerRepository, $customerSession);
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData The address data object.
     * @return bool
     * @throws InputException The specified address belongs to another customer.
     * @throws NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validate(\Magento\Quote\Api\Data\AddressInterface $addressData)
    {
        // Comment code because requirement is display all customer address which same customer code so skip validation
        //$this->doValidate($addressData, $addressData->getCustomerId());
        return true;
    }

    /**
     * Validate address to be used for cart.
     *
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     * @throws InputException The specified address belongs to another customer.
     * @throws NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validateForCart(CartInterface $cart, \Magento\Quote\Api\Data\AddressInterface $address): void
    {
        // Comment code because requirement is display all customer address which same customer code so skip validation
        //$this->doValidate($address, $cart->getCustomerIsGuest() ? null : $cart->getCustomer()->getId());
    }
}

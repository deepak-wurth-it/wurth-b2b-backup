<?php

namespace Wcb\Checkout\Plugin\Model\Address;

class AbstractAddress
{
    public function aroundValidate(\Magento\Customer\Model\Address\AbstractAddress $subject, callable $proceed)
    {
        return true;
    }
}

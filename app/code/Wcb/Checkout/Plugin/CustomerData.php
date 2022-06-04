<?php

namespace Wcb\Checkout\Plugin;

use Magento\Checkout\CustomerData\Cart;
use Wcb\Component\Helper\Data as componentHelper;

class CustomerData
{
    protected $componentHelper;

    /**
     * CustomerData constructor.
     * @param componentHelper $componentHelper
     */
    public function __construct(
        componentHelper $componentHelper
    ) {
        $this->componentHelper = $componentHelper;
    }

    /**
     * @param Cart $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        $result['is_logged_in'] = $this->componentHelper->isLoggedIn();
        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Wcb\CustomerRegistration\Block;

use Magento\Framework\View\Element\Template;

class Success extends Template
{

    /**
     * Success constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getConfirmationLink()
    {
        $email = $this->getRequest()->getParam("email");
        return $this->getUrl("customer/account/confirmation/", ["email" => $email]);
    }
}

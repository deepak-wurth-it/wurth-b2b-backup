<?php

namespace Wcb\Component\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->httpContext = $httpContext;
        parent::__construct($context);
    }

    /**
     * @return mixed|null
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
}

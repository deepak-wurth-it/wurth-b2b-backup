<?php

// Customer login check ajax controller

namespace Wcb\Component\Controller\Customer;

class LoginCheck extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->httpContext = $httpContext;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Customer login status check
     *
     * @return \Magento\Customer\Model\Context::CONTEXT_AUTH
     *
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $result->setData(array('success' => $data));
        return $data;
    }

}

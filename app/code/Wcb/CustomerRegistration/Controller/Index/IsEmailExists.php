<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Controller\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class IsEmailExists extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->storeManager = $storeManager;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        $result['success'] = "false";
        $result['message'] = "";

        $email = $this->getRequest()->getParam("email");

        if ($email) {
            $websiteId = (int)$this->storeManager->getWebsite()->getId();
            $isEmailNotExists = $this->customerAccountManagement->isEmailAvailable($email, $websiteId);

            if ($isEmailNotExists) {
                $result['success'] = "false";
                $result['message'] = "";
            } else {
                $forgotUrl = $this->_url->getUrl("customer/account/forgotpassword");
                $forgotLink = "<a href='$forgotUrl'>" . __('reset your password') . "</a>";
                $result['success'] = "true";
                $result['message'] = __(sprintf("This User already exists. Please try to %s or contact your Sales Representatives.", $forgotLink));
            }
        } else {
            $result['success'] = "false";
            $result['message'] = __("Please add valid email address.");
        }

        $response = $this->_resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }
}

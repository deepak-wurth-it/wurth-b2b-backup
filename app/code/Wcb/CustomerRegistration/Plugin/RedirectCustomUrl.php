<?php
namespace Wcb\CustomerRegistration\Plugin;

use Magento\Customer\Model\Session;

class RedirectCustomUrl
{
    public $_storeManager;
    protected $session;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Session $customerSession
    ) {
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_storeManager=$storeManager;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result
    ) {
        if ($this->session->isLoggedIn()) {
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($baseUrl);
            return $resultRedirect;
        }
        return $result;
    }
}

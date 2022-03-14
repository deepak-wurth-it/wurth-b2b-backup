<?php
namespace Wcb\CustomerRegistration\Plugin;
class RedirectCustomUrl
{
    public $_storeManager;
  public function __construct(
      \Magento\Store\Model\StoreManagerInterface $storeManager
      
    ) {
      
  $this->_storeManager=$storeManager;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result)
    {

       $customUrl= $this->_storeManager->getStore()->getBaseUrl();
       // $customUrl = 'cms/index/index';
        $result->setUrl($customUrl);
		return $result;
    }
}
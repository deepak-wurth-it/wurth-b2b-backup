<?php
namespace Wcb\CustomerRegistration\Plugin;
class RedirectCustomUrl
{
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result)
    {
        $customUrl = 'cms/index/index';
        $result->setPath($customUrl);
		return $result;
    }
}
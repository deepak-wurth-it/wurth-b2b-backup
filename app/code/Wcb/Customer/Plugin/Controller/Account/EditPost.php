<?php

namespace Wcb\Customer\Plugin\Controller\Account;

class EditPost
{
    /**
     * @param \Magento\Customer\Controller\Account\EditPost $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(\Magento\Customer\Controller\Account\EditPost $subject, $result)
    {
        $result->setPath('customer/account');
        return $result;
    }
}

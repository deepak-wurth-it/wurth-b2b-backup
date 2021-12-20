<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Plugin\Magento\Company\Controller\Adminhtml\Index;

class Save
{

    /**
     * @param \Magento\Company\Controller\Adminhtml\Index\Save $subject
     * @param $result
     * @return mixed
     */
    public function afterSetCompanyRequestData(
        \Magento\Company\Controller\Adminhtml\Index\Save $subject,
        $result
    ) {
        $result->setData('number_of_employees', $subject->getRequest()->getPostValue('general')['number_of_employees']);

        $result->setData('division', $subject->getRequest()->getPostValue('general')['division']);

        $result->setData('activities', $subject->getRequest()->getPostValue('general')['activities']);

        return $result;
    }
}


<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Plugin\Magento\Company\Model\Company;

class DataProvider
{

    /**
     * @param \Magento\Company\Model\Company\DataProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetGeneralData(
        \Magento\Company\Model\Company\DataProvider $subject,
        $result,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $result['number_of_employees'] = $company->getData('number_of_employees');

        $result['division'] = $company->getData('division');

        $result['activities'] = $company->getData('activities');

        return $result;
    }
}


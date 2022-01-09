<?php
namespace Wcb\CustomerRegistration\Model\Company;
class DataProvider extends \Magento\Company\Model\Company\DataProvider {
    
    public function getGeneralData(\Magento\Company\Api\Data\CompanyInterface $company) {
        
        $result = parent::getGeneralData($company);
        
        # add custom column value to the General data section so that the value populates the custom field on the admin company edit page
        $result['division'] = $company->getDivision();
        $result['number_of_employees'] = $company->getNumberOfEmployees();
        $result['activities'] = $company->getActivities();
        
        return $result;
    }
}
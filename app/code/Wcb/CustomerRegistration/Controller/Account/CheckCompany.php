<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CheckCompany extends \Magento\Framework\App\Action\Action
{
    protected $companyCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\CompanyManagementInterface $companyMngRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Company\Model\ResourceModel\Company\CollectionFactory $companyCollection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyMngRepository = $companyMngRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->companyCollection = $companyCollection;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $customerCode = $this->getRequest()->getParam('customer_code');
        $companyOib = $this->getRequest()->getParam('CompanyOib');

        //Check customer exists
        $exists = $this->checkCompanyExists($companyOib, $customerCode);
        $result = [];
        if ($exists['company_code_exists']) {
            $result["success"] = 'true';
            $result["message"] = "";

            if ($exists['customer_type'] == '1') {
                $result["success"] = 'false';
                $result["message"] = __("You're not allowed to register.");
            }
        } else {
            $result["success"] = 'false';
            $result["message"] = __("Customer is not linked. Please enter valid customer code");
        }

        return $resultJson->setData($result);
    }
    public function checkCompanyExists($companyOib, $customerCode)
    {
        $companies = $this->companyCollection->create()
            ->addFieldToFilter("vat_tax_id", ["eq" => $companyOib]);

        $companyCodeExists = false;
        $customerType = '';
        $returnData = [];
        foreach ($companies as $company) {
            if ($company->getId()) {
                $customer = $this->checkCustomerExist($company->getSuperUserId());
                if ($customer != '') {
                    if ($customer->getCustomAttribute("customer_code")) {
                        $custCode = $customer
                            ->getCustomAttribute("customer_code")
                            ->getValue();
                        if ($customerCode == $custCode) {
                            if ($customer->getCustomAttribute("wc_customer_type")) {
                                $customerType = $customer
                                    ->getCustomAttribute("wc_customer_type")
                                    ->getValue();
                            }
                            $companyCodeExists = true;
                            break;
                        }
                    }
                }
            }
        }

        $returnData['company_code_exists'] = $companyCodeExists;
        $returnData['customer_type'] = $customerType;
        return $returnData;
    }
    public function checkCustomerExist($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            return '';
        }
    }
}

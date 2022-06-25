<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class VerifyCompany extends \Magento\Framework\App\Action\Action
{
    protected $customerRepositoryInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\CompanyManagementInterface $companyMngRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyMngRepository = $companyMngRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $companyOib = $this->getRequest()->getParam('CompanyOib');

        if ($companyOib == '') {
            return $resultJson->setData([
                'compid' => '',
                'cid' => '',
                'html' => '',
                'success' => ''
            ]);
        } elseif ($companyOib != '') {
            $companyDetails = $this->getCompanyId($companyOib);
            if ($companyDetails) {
                $cId = $companyDetails->getId();
                $success = ($companyDetails->getCompanyName() !='') ? true : false;
            } else {
                return $resultJson->setData([
                    'compid' => '',
                    'cid' => '',
                    'html' => '',
                    'success' => ''
                ]);
            }
        }

        return $resultJson->setData([
            'cid' => $cId,
            'html' => $companyDetails->getCompanyName(),
            'success' => $success
        ]);
    }

    /**
     * @param string $companyTax
     * @return mixed
     * @throws LocalizedException
     */
    public function getCompanyId(string $companyTax)
    {
        $this->searchCriteriaBuilder->addFilter(
            'vat_tax_id',
            trim($companyTax)
        );
        $companyData = $this->companyRepository->getList(
            $this->searchCriteriaBuilder->create()
        );//->getItems();
        $companyDetails = null;
        if ($companyData) {
            if ($companyData->getTotalCount() > 1) {
                $companyData = $companyData->getItems();
                foreach ($companyData as $company) {
                    $customer = $this->checkCustomerExist($company->getSuperUserId());
                    if ($customer != '') {
                        if ($customer->getCustomAttribute("wc_customer_type")) {
                            $customerType = $customer
                                ->getCustomAttribute("wc_customer_type")
                                ->getValue();
                            if ($customerType == '1') {
                                $companyDetails = $company;
                                break;
                            }
                        }
                    }
                    $companyDetails = $company;//->getCompanyName();
                }
            } else {
                $companyData = $companyData->getItems();
                foreach ($companyData as $company) {
                    $companyDetails = $company;//->getCompanyName();
                }
            }
        }
        return $companyDetails;
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

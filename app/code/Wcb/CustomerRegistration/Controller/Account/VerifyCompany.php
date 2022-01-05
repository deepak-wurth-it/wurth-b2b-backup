<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\CompanyRepositoryInterface;

class VerifyCompany extends \Magento\Framework\App\Action\Action
{
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

        if($companyOib == ''){
            return $resultJson->setData([
                'compid' => '',
                'cid' => '',
                'html' => '',
                'success' => ''
            ]);
        }elseif($companyOib != ''){
            $companyDetails = $this->getCompanyId($companyOib);
    
            $cId = $companyDetails->getId();
            $success = ($companyDetails->getCompanyName() !='')?true:false;
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
        )->getItems();
        $companyDetails = null;
        if ($companyData) {
            foreach ($companyData as $company) {
                $companyDetails = $company;//->getCompanyName();
            }
        }
        return $companyDetails;
    }    
}
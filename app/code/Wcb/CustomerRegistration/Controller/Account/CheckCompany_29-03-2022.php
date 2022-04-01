<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CheckCompany extends \Magento\Framework\App\Action\Action
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
        $customerCode = $this->getRequest()->getParam('customer_code');
        $companyOib = $this->getRequest()->getParam('CompanyOib');

        //Check customer exists
        $exists = $this->checkCustomerExists($customerCode);

        if ($customerCode =='' || $companyOib == '' || $exists == '') {
            return $resultJson->setData([
                'compid' => '',
                'cid' => '',
                'html' => '',
                'success' => ''
            ]);
        } elseif ($customerCode !='' &&  $companyOib != '' && $exists != '') {
            $getCompanyId = $this->getCustomerCompany($customerCode);
            $companyDetails = $this->getCompanyId($companyOib);

            $cId = $companyDetails->getId();
            $success = ($companyDetails->getCompanyName() !='') ? true : false;
        }

        return $resultJson->setData([
            'compid' => $getCompanyId,
            'cid' => $cId,
            'html' => $companyDetails->getCompanyName(),
            'success' => $success
        ]);
    }

    public function getCustomerCompany($customerId)
    {
        $company = $this->companyMngRepository->getByCustomerId($customerId)->getId();
        return $company;
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

    /**
    * Get customer by Id.
    *
    * @param int $customerId
    *
    * @return \Magento\Customer\Model\Data\Customer
    */
    public function checkCustomerExists($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            //$this->logger->critical($e);
            return '';
        }
    }
}

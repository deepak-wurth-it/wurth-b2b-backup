<?php

namespace Wcb\CustomerRegistration\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Company\Model\CompanySuperUserGet;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

class Save extends \Magento\Company\Controller\Adminhtml\Index\Save
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Company::manage';

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var CompanySuperUserGet
     */
    private $superUser;

    /**
     * @var CompanyInterfaceFactory
     */
    private $companyDataFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    protected $accountManagement;
    protected $customer;

    public function __construct(
        Context $context,
        DataObjectProcessor $dataObjectProcessor,
        CompanySuperUserGet $superUser,
        CompanyRepositoryInterface $companyRepository,
        CompanyInterfaceFactory $companyDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        CustomerFactory $customer
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->superUser = $superUser;
        $this->companyRepository = $companyRepository;
        $this->companyDataFactory = $companyDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->customer = $customer;
        parent::__construct($context, $dataObjectProcessor, $superUser, $companyRepository, $companyDataFactory, $dataObjectHelper);
    }

    /**
     * Create or save customer group.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var CompanyInterface $company */
        $company = null;
        $request = $this->getRequest();
        $id = $request->getParam('id') ? $request->getParam('id') : null;
        try {
            $company = $this->saveCompany($id);

            // After save
            $this->_eventManager->dispatch(
                'adminhtml_company_save_after',
                ['company' => $company, 'request' => $request]
            );

            $companyData = ['companyName' => $company->getCompanyName()];
            $this->messageManager->addSuccessMessage(
                $id
                    ? __('You have saved company %companyName.', $companyData)
                    : __('You have created company %companyName.', $companyData)
            );
            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
        } catch (LocalizedException $e) {
            $returnToEdit = true;
            $this->messageManager->addErrorMessage($e->getMessage());
            if ($company instanceof CompanyInterface) {
                $this->storeCompanyDataToSession($company);
            }
        } catch (Exception $e) {
            $returnToEdit = true;
            $this->messageManager->addExceptionMessage($e, __('Something went wrong. Please try again later.'));
            if ($company instanceof CompanyInterface) {
                $this->storeCompanyDataToSession($company);
            }
        }
        return $this->getRedirect($returnToEdit, $company);
    }

    /**
     * Create/load company, set request data, set default role for a new company.
     *
     * @param int $id
     * @return CompanyInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function saveCompany($id)
    {
        $data = $this->extractData();
        $customerData = $this->extractCustomerData();
        $customer = $this->superUser->getUserForCompanyAdmin($customerData);
        if ($id !== null) {
            $company = $this->companyRepository->get((int)$id);
        } else {
            $company = $this->companyDataFactory->create();
        }
        $this->setCompanyRequestData($company, $data);
        $company->setSuperUserId($customer->getId());
        $this->companyRepository->save($company);
        return $company;
    }

    /**
     * Filter request to get just list of fields.
     *
     * @return array
     */
    private function extractData()
    {
        $allFormFields = [
            CompanyInterface::COMPANY_ID,
            CompanyInterface::STATUS,
            CompanyInterface::NAME,
            CompanyInterface::LEGAL_NAME,
            CompanyInterface::COMPANY_EMAIL,
            CompanyInterface::EMAIL,
            CompanyInterface::VAT_TAX_ID,
            CompanyInterface::RESELLER_ID,
            CompanyInterface::COMMENT,
            CompanyInterface::STREET,
            CompanyInterface::CITY,
            CompanyInterface::COUNTRY_ID,
            CompanyInterface::REGION,
            CompanyInterface::REGION_ID,
            CompanyInterface::POSTCODE,
            CompanyInterface::TELEPHONE,
            CompanyInterface::JOB_TITLE,
            CompanyInterface::PREFIX,
            CompanyInterface::FIRSTNAME,
            CompanyInterface::MIDDLENAME,
            CompanyInterface::LASTNAME,
            CompanyInterface::SUFFIX,
            CompanyInterface::GENDER,
            CompanyInterface::CUSTOMER_GROUP_ID,
            CompanyInterface::SALES_REPRESENTATIVE_ID,
            CompanyInterface::REJECT_REASON,
            CustomerInterface::WEBSITE_ID,
            'extension_attributes',
        ];
        $result = [];
        $request = $this->getRequest()->getParams();
        unset($request['use_default']);
        if (is_array($request)) {
            foreach ($request as $fields) {
                if (!is_array($fields)) {
                    continue;
                }
                $result = array_merge_recursive($result, $fields);
            }
        }
        $result = array_intersect_key($result, array_flip($allFormFields));
        return $result;
    }

    /**
     * Filter customer-related data from request
     *
     * @return array
     */
    private function extractCustomerData(): array
    {
        $data = [];
        $requestParams = $this->getRequest()->getParams();
        if (is_array($requestParams)
            && !empty($requestParams['company_admin'])
            && is_array($requestParams['company_admin'])) {
            $data = $requestParams['company_admin'];
        }
        $data = $this->checkCustomerConfirm($data);

        return $data;
    }

    public function checkCustomerConfirm($data)
    {
        if (isset($data['email'], $data['website_id'])) {
            $customer = $this->customer->create()->setWebsiteId($data['website_id'])->loadByEmail($data['email']);
            if ($customer) {
                $customer_id = $customer->getId();
                if (isset($data['confirmation'])) {
                    if ($data['confirmation'] === '' || $data['confirmation'] === null) {
                        $data['confirmation'] = $this->accountManagement->getConfirmationStatus($customer_id);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Populate company object with request data.
     *
     * @param CompanyInterface $company
     * @param array $data
     * @return CompanyInterface
     */
    public function setCompanyRequestData(CompanyInterface $company, array $data)
    {
        $this->dataObjectHelper->populateWithArray(
            $company,
            $data,
            CompanyInterface::class
        );
        return $company;
    }

    /**
     * Store Customer Group Data to session.
     *
     * @param CompanyInterface $company
     * @return void
     */
    private function storeCompanyDataToSession(CompanyInterface $company)
    {
        $companyData = $this->dataObjectProcessor->buildOutputDataArray(
            $company,
            CompanyInterface::class
        );
        $this->_getSession()->setCompanyData($companyData);
    }

    /**
     * Get redirect object depending on $returnToEdit and is company new.
     *
     * @param bool $returnToEdit
     * @param CompanyInterface|null $company [optional]
     *
     * @return Redirect
     */
    private function getRedirect($returnToEdit, CompanyInterface $company = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if (($company != null) && $company->getId()) {
                $resultRedirect->setPath(
                    'company/index/edit',
                    ['id' => $company->getId()]
                );
            } else {
                $resultRedirect->setPath(
                    'company/index/new'
                );
            }
        } else {
            $resultRedirect->setPath('company/index');
        }
        return $resultRedirect;
    }
}

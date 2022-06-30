<?php

namespace Wcb\Customer\Block\Account\Dashboard;

use Exception;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Wcb\Customer\Model\ResourceModel\WurthnavEmployees\CollectionFactory as employeeFactory;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * @var Session
     */
    protected $customer;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;
    /**
     * @var employeeFactory
     */
    protected $employeeFactory;
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Info constructor.
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param CurrentCustomer $currentCustomer
     * @param View $helperView
     * @param Session $customer
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param employeeFactory $employeeFactory
     * @param CustomerCollectionFactory $CustomerCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        CurrentCustomer $currentCustomer,
        View $helperView,
        Session $customer,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        employeeFactory $employeeFactory,
        CustomerCollectionFactory $CustomerCollectionFactory,
        array $data = []
    ) {
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->employeeFactory = $employeeFactory;
        $this->customerCollectionFactory = $CustomerCollectionFactory;
        parent::__construct($context, $currentCustomer, $subscriberFactory, $helperView, $data);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getInfoDetail()
    {
        $data = [];
        $data['sales_person_name'] = 'Centrala';
        $data['sales_person_email'] = 'wuerth@wuerth.com.hr';

        $customerData = $this->getCustomerById($this->customer->getCustomerId());
        if ($customerData->getId()) {
            $company = $this->getCompany($customerData);
            if ($company) {
                if ($customerData->getCustomAttribute("customer_code")) {
                    $data['customer_code'] = $customerData->getCustomAttribute("customer_code")->getValue();
                    $sameCustomerCodeCount = $this->customerCollectionFactory->create()
                        ->addAttributeToFilter("customer_code", ['eq' => $data['customer_code']]);
                    $data['same_customer_code_count'] = $sameCustomerCodeCount->count();
                }
                $data['company_name'] = $company->getCompanyName();
                $companySalesPersonCode = $company->getWcbSalesPersonCode();
                if ($companySalesPersonCode) {
                    $employeeData = $this->employeeFactory->create()
                        ->addFieldToFilter("EmployeeCode", ['eq' => $companySalesPersonCode])
                        ->getFirstItem();

                    if ($employeeData->getId()) {
                        $data['sales_person_name'] = $employeeData->getData('Name');
                        $data['sales_person_email'] = $employeeData->getData('Email');
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $customerId
     * @return CustomerInterface|null
     */
    public function getCustomerById($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (LocalizedException $exception) {
            $customer = null;
        }

        return $customer;
    }

    /**
     * @param $customer
     * @return bool|CompanyInterface
     */
    public function getCompany($customer)
    {
        try {
            if ($customer->getExtensionAttributes()->getCompanyAttributes()) {
                $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                return $this->companyRepository->get($companyId);
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

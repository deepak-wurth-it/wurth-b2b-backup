<?php

namespace Wurth\Theme\Model;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CompanyDetail implements ConfigProviderInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var SessionFactory
     */
    protected $customerSession;
    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * CompanyDetail constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param SessionFactory $customerSession
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        AddressRepositoryInterface $addressRepositoryInterface,
        SessionFactory $customerSession,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->customerRepository = $customerRepositoryInterface;
        $this->addressRepository = $addressRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['company_detail'] = $this->getDefaultBillAddress();
        return $config;
    }

    /**
     * @return array|bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultBillAddress()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById($customerSession->getCustomerId());
                $company = $this->getCompany($customer);
                // get Super user using current user
                $customer = $this->customerRepository->getById($company->getSuperUserId());
                $billingAddressId = $customer->getDefaultBilling();
                $billingAddress = $this->addressRepository->getById($billingAddressId);
                $customerCode = '';
                $companyName = '';
                if ($company) {
                    $companyName = $company->getCompanyName();
                }
                if ($customer->getCustomAttribute("customer_code")) {
                    $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
                }

                // if found bill to customer no
                if ($billingAddress->getCustomAttribute('bill_to_customer_code') &&
                    $billingAddress->getCustomAttribute('is_bill_to_customer_number')) {
                    $isBillToCustomerNumber = $billingAddress->getCustomAttribute("is_bill_to_customer_number")->getValue();
                    $billToCustomerCode = $billingAddress->getCustomAttribute("bill_to_customer_code")->getValue();
                    if ($isBillToCustomerNumber && $billToCustomerCode) {
                        $companyName = $billingAddress->getFirstname() . " " . $billingAddress->getLastname();
                        $customerCode = $billToCustomerCode;
                    }
                }
                $companyName .= " (" . $customerCode . ")";
                $addressData = [];
                $addressData['name'] = $companyName;
                $addressData['city'] = $billingAddress->getCity();
                $addressData['street'] = $billingAddress->getStreet();
                $addressData['postcode'] = $billingAddress->getPostCode();
                return $addressData;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
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

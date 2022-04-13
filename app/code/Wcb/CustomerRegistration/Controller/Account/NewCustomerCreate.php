<?php
namespace Wcb\CustomerRegistration\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Wcb\CustomerRegistration\Model\ResourceModel\Division\CollectionFactory as DivisionCollection;

class NewCustomerCreate extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $divisionCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Company\Api\Data\CompanyInterface $companyInterface,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        DivisionCollection $divisionCollection,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->companyRepository = $companyRepository;
        $this->companyInterface = $companyInterface;
        $this->objectHelper = $objectHelper;
        $this->customerRepository = $customerRepository;
        $this->divisionCollection = $divisionCollection;
        return parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $email = $data['confirm_email'];
        $customerCreate = $this->createCustomer($data);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($customerCreate) {
            $resultRedirect->setPath("excustomer/account/success/", ['email' => $email]);
        } else {
            $resultRedirect->setPath("customer/account/create/");
        }
        return $resultRedirect;
    }

    public function createCompany($request, $customerId)
    {
        $companyRepo = $this->companyRepository;
        $companyObj = $this->companyInterface;
        $dataObj = $this->objectHelper;
        $company = [
                    "company_name" => $request['company']['company_name'],
                    "company_email" => $request['confirm_email'],
                    "email" => $request['confirm_email'],
                    "street" => $request['company']['street'],
                    "vat_tax_id" => $request['company']['vat_tax_id'],
                    "city" => $request['company']['city'],
                    "country_id" => 'HR',
                    "region" => $request['region'],
                    "region_id" => $request['region'],
                    "postcode" => $request['company']['postcode'],
                    "telephone" => "+385" . $request['telephone'],
                    "super_user_id" => $customerId,
                    "position" => $request['position'],
                    "customer_group_id" => 1,
                    "number_of_employees" => $request['company']['no_of_employees'],
                    "division" => $this->getDivisionNameByGroupId($request['company']['division']),
                    "activities" => $request['company']['activities'],
                    "firstname" => $request['firstname'],
                    "lastname" => $request['lastname']
                ];

        $dataObj->populateWithArray($companyObj, $company, \Magento\Company\Api\Data\CompanyInterface::class);

        $companyObj->setNumberOfEmployees($request['company']['no_of_employees']);
        $companyObj->setDivision($this->getDivisionNameByGroupId($request['company']['division']));
        $companyObj->setActivities($request['company']['activities']);
        return $companyRepo->save($companyObj);
    }
    public function getDivisionNameByGroupId($groupId)
    {
        $divisionData = $this->divisionCollection->create()
            ->addFieldToFilter("customer_group_id", ["eq" => $groupId])
            ->getFirstItem();
        $divisionName = "";
        if ($divisionData->getId()) {
            $divisionName = $divisionData->getName();
        }
        return $divisionName;
    }
    /** Create customer
     *  Pass customer data as array
     */
    public function createCustomer($data)
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($data['email']);// load customer by email to check if customer is availalbe or not
        if (!$customer->getId()) {
            /* create customer */
            $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setPosition($data['position'])
                    ->setMobile("+385" . $data['telephone'])
                    ->setPhone("+385" . $data['telephone'])
                    ->setTaxvat($data['company']['vat_tax_id'])
                    //->setCustomerCode($data['telephone'])
                    ->setCustomerCode("")
                    ->setEmail($data['email'])
                    ->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED)
                    ->setPassword($data['password']);

            $customerSave = $customer->save();
            $companySave = $this->createCompany($data, $customer->getId());
            $customerId = $customer->getId();
            if ($customerId && $data['company']['division']) {
                $this->updateCustomerGroup($customerId, $data['company']['division']);
            }
            if ($customer && $companySave) {
                $this->messageManager->addSuccess(__('Customer and Company created successfully.'));
            }
            if (array_key_exists('daddress', $data)) {
                $this->saveAddress($data, 'delivery', $customerId);
            }

            if (array_key_exists('iaddress', $data)) {
                $this->saveAddress($data, 'invoice', $customerId);
            }
            return true;
        } else {
            $this->messageManager->addError(__('This User already exists. Please try to reset your PW and PW Link or contact your Sales Rep.'));
            return false;
        }
    }

    public function saveAddress($data, $type, $cusId)
    {
        $addressToSave = '';

        $defaultCountryCode = 'HR';
        $sameAsHeadQuartersDelAd = $sameAsHeadQuartersInvAd = 0;
        if ($type === 'delivery') {
            $addressToSave = 'daddress';
            $sameAsHeadQuartersDelAd = isset($data['daddress']['da_same_as_hq_address']) ? 1 : 0;
            $prefix = 'da_';
            if ($sameAsHeadQuartersDelAd == 1) {
                $prefix = "";
                $addressToSave = "company";
            }
        } elseif ($type === 'invoice') {
            $addressToSave = 'iaddress';
            $sameAsHeadQuartersInvAd = isset($data['iaddress']['ia_same_as_hq_address']) ? 1 : 0;
            $prefix = 'ia_';
            if ($sameAsHeadQuartersInvAd == 1) {
                $prefix = "";
                $addressToSave = "company";
            }
        }

        /* save address as customer */
        $address = $this->dataAddressFactory->create();
        $address->setFirstname($data['firstname']);
        $address->setLastname($data['lastname']);
        $address->setTelephone("+385" . $data['telephone']);

//        $street[] = $data[$addressToSave]['street'];//pass street as array
        $address->setStreet([$data[$addressToSave][$prefix . 'street']]);
        $address->setCity($data[$addressToSave][$prefix . "city"]);
        $address->setPostcode($data[$addressToSave][$prefix . "postcode"]);
        if ($prefix == "") {
            $address->setRegionId($data["region"]);
        } else {
            $address->setRegionId($data[$addressToSave][$prefix . "region"]);
        }

        $address->setCountryId($defaultCountryCode);
        $address->setIsDefaultShipping($sameAsHeadQuartersDelAd);
        $address->setIsDefaultBilling($sameAsHeadQuartersInvAd);
        $address->setCustomerId($cusId);
        try {
            $this->addressRepository->save($address);
        } catch (\Exception $e) {
            return __('Error in shipping/billing address.');
        }
    }

    /**
    * Save Customer group
    * @param int $customerId
    * @param int $groupId
    * @return void
    */
    public function updateCustomerGroup(int $customerId, int $groupId): void
    {
        $customer = $this->getCustomerById($customerId);

        if ($customer) {
            try {
                $customer->setGroupId($groupId);
                $this->customerRepository->save($customer);
            } catch (LocalizedException $exception) {
                $this->logger->error($exception);
            }
        }
    }

    /**
     * Get Customer By Id
     * @param int $customerId
     * @return CustomerInterface|null
     */
    public function getCustomerById(int $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (LocalizedException $exception) {
            $customer = null;
            $this->logger->error($exception);
        }

        return $customer;
    }
}

<?php
namespace Wcb\CustomerRegistration\Controller\Account;

class NewCustomerCreate extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

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
        array $data = []){
		$this->_pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->companyRepository = $companyRepository;
        $this->companyInterface = $companyInterface;
        $this->objectHelper = $objectHelper;
        return parent::__construct($context);
	}

	public function execute()
	{
        $data = $this->getRequest()->getPostValue();
        $this->createCustomer($data);
		return $this->_pageFactory->create();
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
                    "city" => $request['company']['city'],
                    "country_id" => 'HR',//$request['company_country'],
                    "region" => $request['region'],
                    "region_id" => $request['region'],
                    "postcode" => $request['company']['postcode'],
                    "telephone" => $request['telephone'],
                    "super_user_id" => $customerId,
                    "position" => $request['position'],
                    "customer_group_id" => 1,
                    "number_of_employees" => $request['company']['no_of_employees'],
                    "division" => $request['company']['division'],
                    "activities" => $request['company']['activities'],
                    "firstname" => $request['firstname'],
                    "lastname" => $request['lastname'],
                ];

        $dataObj->populateWithArray($companyObj, $company, \Magento\Company\Api\Data\CompanyInterface::class);
        return $companyRepo->save($companyObj);
    }

    /** Create customer
     *  Pass customer data as array
     */
    public function createCustomer($data) {
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($data['email']);// load customer by email to check if customer is availalbe or not
        if(!$customer->getId()){
            /* create customer */
            $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setPosition($data['position'])
                    ->setMobile($data['telephone'])
                    ->setEmail($data['email'])
                    ->setPassword($data['password']);
            $customer->save();
            $this->createCompany($data, $customer->getId());

            $this->saveAddress($data, 'delivery', $customer->getId());
            $this->saveAddress($data, 'invoice', $customer->getId());

        } else {
            return __('Customer is already exist!');
        }
    }

    public function saveAddress($data, $type, $cusId)
    {
        $addressToSave = '';

        $defaultCountryCode = 'HR';
        $sameAsHeadQuartersDelAd = $sameAsHeadQuartersInvAd = 0;
        if($type == 'delivery'){
            $addressToSave = 'daddress';
            $sameAsHeadQuartersDelAd = ($data['daddress']['da_same_as_hq_address'])? 1 : 0;
            $prefix = 'da_';
        }elseif($type == 'invoice'){
            $addressToSave = 'iaddress';
            $sameAsHeadQuartersInvAd = ($data['ia_same_as_hq_address']) ? 1 : 0;
            $prefix = 'ia_';
        }

        /* save address as customer */
        $address = $this->dataAddressFactory->create();
        $address->setFirstname($data['firstname']);
        $address->setLastname($data['lastname']);
        $address->setTelephone($data['telephone']);

//        $street[] = $data[$addressToSave]['street'];//pass street as array
        $address->setStreet([$data[$addressToSave][$prefix.'street']]);

        $address->setCity($data[$addressToSave][$prefix."city"]);
        $address->setCountryId($defaultCountryCode);
        $address->setPostcode($data[$addressToSave][$prefix."postcode"]);
        $address->setRegionId($data[$addressToSave][$prefix."region"]);
        $address->setIsDefaultShipping($sameAsHeadQuartersDelAd);
        $address->setIsDefaultBilling($sameAsHeadQuartersInvAd);
        $address->setCustomerId($cusId);
        try
        {
            $this->addressRepository->save($address);  
        }
        catch (\Exception $e) {
            return __('Error in shipping/billing address.');
        }
    }
    
}
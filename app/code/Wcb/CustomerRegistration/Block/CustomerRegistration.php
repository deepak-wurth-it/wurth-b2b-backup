<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\CustomerRegistration\Block;

//use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

class CustomerRegistration extends \Magento\Framework\View\Element\Template
{
    protected $_regionFactory;
    protected $divisionCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroup,
        \Magento\Directory\Model\Country $country,
        \Wcb\CustomerRegistration\Model\ResourceModel\Division\CollectionFactory $divisionCollection,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->customerGroup = $customerGroup;
        $this->country = $country;
        $this->divisionCollection = $divisionCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve customer group collection
     *
     * @return GroupCollection
     */
    public function getCustomerGroups()
    {
        $customerGroups = $this->customerGroup->toOptionArray();
        return $customerGroups;
    }

    public function getDivision()
    {
        return $this->customerGroup->create()
            ->addFieldToFilter("branch_code", ["neq" => 'NULL']);
    }

    /**
     * Get the list of regions present in the given Country
     * Returns empty array if no regions available for Country
     *
     * @param String
     * @return Array/Void
    */
    public function getRegionsOfCountry($countryCode)
    {
        $regionCollection = $this->country->loadByCode($countryCode)->getRegions();
        $regions = $regionCollection->loadData()->toOptionArray(false);
        return $regions;
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
        $customer->loadByEmail($data['customer']['email']);// load customer by email to check if customer is availalbe or not
        if (!$customer->getId()) {
            /* create customer */
            $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($data['customer']['firstname'])
                    ->setLastname($data['customer']['lastname'])
                    ->setPrefix($data['customer']['prefix'])
                    ->setMobile($data['customer']['mobile'])
                    ->setEmail($data['customer']['email'])
                    ->setPassword($data['customer']['password']);
            $customer->save();

            /* save address as customer */
            $address = $this->dataAddressFactory->create();
            $address->setFirstname($data['address']['firstname']);
            $address->setLastname($data['address']['lastname']);
            $address->setTelephone($data['address']['telephone']);

            $street[] = $data['address']['street'];//pass street as array
            $address->setStreet($street);

            $address->setCity($data['address']['city']);
            $address->setCountryId($data['address']['country_id']);
            $address->setPostcode($data['address']['postcode']);
            $address->setRegionId($data['address']['region_id']);
            $address->setIsDefaultShipping(1);
            $address->setIsDefaultBilling(1);
            $address->setCustomerId($customer->getId());
            try {
                $this->addressRepository->save($address);
            } catch (\Exception $e) {
                return __('Error in shipping/billing address.');
            }
        } else {
            return __('Customer is already exist!');
        }
    }
}

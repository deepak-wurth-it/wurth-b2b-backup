<?php

namespace Wcb\RequisitionList\Block;

use DateTime;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Collection;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\CollectionFactory as RequisitionListFactory;
use Wcb\Customer\Helper\Data as CustomerHelper;

class RequisitionList extends Template
{
    protected $requisitionList;
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    protected $storeCustomer;

    /**
     * RequisitionList constructor.
     * @param Template\Context $context
     * @param RequisitionListFactory $requisitionListFactory
     * @param TimezoneInterface $timezone
     * @param CustomerHelper $customerHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        RequisitionListFactory $requisitionListFactory,
        TimezoneInterface $timezone,
        CustomerHelper $customerHelper,
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->customerHelper = $customerHelper;
        $this->requisitionList = $requisitionListFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRequisitionList()
    {
        $customerCode = $this->getCustomerCode();
        $sameCustomerCodeCustomers = $this->customerHelper->getCustomerByCustomerCode($customerCode);
        foreach ($sameCustomerCodeCustomers as $_customer) {
            $this->storeCustomer[$_customer->getId()] = $_customer->getName();
        }
        return $this->requisitionList->create()
            ->addFieldToFilter("customer_id", ["in", $sameCustomerCodeCustomers->getAllIds()])
            ->setOrder('entity_id', 'DESC');
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerCode()
    {
        $customerCode = "";
        $customer = $this->getCurrentCustomer();
        if ($customer) {
            if ($customer->getCustomAttribute("customer_code")) {
                $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
            }
        }

        return $customerCode;
    }

    /**
     * @return bool|CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomer()
    {
        return $this->customerHelper->getCurrentCustomer();
    }

    /**
     * @param $dateTime
     * @return false|string
     */
    public function getCreateDateFormat($dateTime)
    {
        return date('d/m/Y', strtotime($dateTime));
    }

    /**
     * @param $dateTime
     * @return string
     */
    public function getCreateDateTimeFormat($dateTime)
    {
        return $this->timezone->date(new DateTime($dateTime))->format('Y.m.d H:i');
    }

    /**
     * @param $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $customerName = '';
        if (isset($this->storeCustomer[$customerId])) {
            $customerName = $this->storeCustomer[$customerId];
        }
        return $customerName;
    }
}

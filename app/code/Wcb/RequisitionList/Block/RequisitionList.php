<?php

namespace Wcb\RequisitionList\Block;

use DateTime;
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

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Pagination'));
        if ($this->getRequisitionList()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.requisition.pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20, 50 => 50])
                ->setShowPerPage(true)->setCollection(
                    $this->getRequisitionList()
                );
            $this->setChild('pager', $pager);
            $this->getRequisitionList()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
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
        $currentPage = $this->getRequest()->getParam('p');
        $currentLimit = $this->getRequest()->getParam('limit');
        $page = ($currentPage) ? $currentPage : 1;
        $pageSize = ($currentLimit) ? $currentLimit : 10;

        return $this->requisitionList->create()
            ->addFieldToFilter("customer_id", ["in", $sameCustomerCodeCustomers->getAllIds()])
            ->setOrder('entity_id', 'DESC')
            ->setPageSize($pageSize)
            ->setCurPage($page);
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
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCompanyAndCustomerCode()
    {
        $customerCode = "";
        $companyName = "";
        $customer = $this->getCurrentCustomer();
        if ($customer) {
            $company = $this->customerHelper->getCompany($customer);
            if ($customer->getCustomAttribute("customer_code")) {
                $customerCode = $customer->getCustomAttribute("customer_code")->getValue();
            }
            if ($company) {
                $companyName = $company->getCompanyName();
            }
        }
        return [
            "customer_code" => $customerCode,
            "company_name" => $companyName,
        ];
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
     * @return stringwc
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

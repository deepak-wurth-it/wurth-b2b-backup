<?php

namespace Wcb\Customer\Helper;

use Exception;
use Magento\Authorization\Model\CompositeUserContext;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends AbstractHelper
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    /**
     * @var CompositeUserContext
     */
    protected $compositeUserContext;
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     * @param Context $context
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerRepository $customerRepository
     * @param CompositeUserContext $compositeUserContext
     * @param SessionFactory $customerSession
     */
    public function __construct(
        Context $context,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerRepository $customerRepository,
        CompositeUserContext $compositeUserContext,
        SessionFactory $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->compositeUserContext = $compositeUserContext;
        parent::__construct($context);
    }

    /**
     * @param $customerCode
     * @return Collection
     * @throws LocalizedException
     */
    public function getCustomerByCustomerCode($customerCode)
    {
        return $this->customerCollectionFactory->create()
            ->addAttributeToFilter("customer_code", ['eq' => $customerCode]);
    }

    /**
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomer()
    {
        try {
            return $this->customerRepository->getById($this->customerSession->create()->getCustomer()->getId());
        } catch (Exception $e) {
            return false;
        }
    }
}

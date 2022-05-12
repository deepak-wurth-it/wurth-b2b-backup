<?php

namespace Wcb\Base\Helper;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\UserFactory;

class Data extends AbstractHelper
{
    /**
     * @var ProductFactory
     */
    protected $productLoader;
    /**
     * @var
     */
    protected $connection;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var UserFactory
     */
    private $userFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Data constructor.
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param ProductFactory $productFactory
     * @param Session $customerSession
     * @param UserContextInterface $userContext
     * @param UserFactory $userFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Context $context
     */
    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface,
        ProductFactory $productFactory,
        Session $customerSession,
        UserContextInterface $userContext,
        UserFactory $userFactory,
        CustomerRepositoryInterface $customerRepository,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productRepositoryInterface;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    public function getLoadProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = 0;
        if ($this->_customerSession->isLoggedIn()) {
            try {
                $customerGroupId = $this->_customerSession->getCustomerGroupId();
            } catch (NoSuchEntityException $e) {
            } catch (LocalizedException $e) {
            }
        }
        return $customerGroupId;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerApiGroupId()
    {
        $customerGroupId = 0;
        $userId = $this->userContext->getUserId();
        $customer = $this->customerRepository->getById($userId);
        if ($customer->getGroupId()) {
            return $customer->getGroupId();
        }
        return $customerGroupId;
    }
}

<?php

namespace Wcb\Base\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

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

    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface,
        ProductFactory $productFactory,
        Session $customerSession,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productRepositoryInterface;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getLoadProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    public function getCustomerGroupId()
    {
        $customerGroupId = 0;
        if ($this->_customerSession->isLoggedIn()) {
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }
        return $customerGroupId;
    }
}

<?php

namespace Wcb\Base\Helper;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Wurth\Shippingproduct\Helper\Data as ShippingHelper;

class Data extends AbstractHelper
{
    const API_FEEDBACK_EMAIL = 'apiconfig/config/application_feedback_email';
    const API_GENERAL_CONDITION_URL = 'apiconfig/config/general_conditions_url';
    const API_PRIVACY_POLICY_URL = 'apiconfig/config/privacy_policy_url';
    const API_CATEGORY_MEDIA_URL = 'apiconfig/config/category_media_url';
    const FLIP_CATALOG_URL = 'catalog_settings/catalog_config/flip_catalog_url';
    const MINIMUM_SHIPPING_AMT = 'shipping_product_section/general/cart_amount_limit';
    const SHIPPING_PRODUCT_CODE = 'shipping_product_section/general/product_code';

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
     * @var ScopeConfigInterface
     */
    private $_scopeConfigInterface;
    /**
     * @var ShippingHelper
     */
    private $_shppingHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

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
        ScopeConfigInterface $scopeConfigInterface,
        ShippingHelper $_shippingHelper,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        Context $context
    ) {
        $this->productLoader = $productFactory;
        $this->productRepository = $productRepositoryInterface;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
        $this->_scopeConfigInterface = $scopeConfigInterface;
        $this->_shppingHelper = $_shippingHelper;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param $id
     * @return ProductInterface
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

    /**
     * @return array
     */
    public function getApiMobileConfiguration()
    {
        $data = [];
        $data['base_url'] = $this->getBaseUrl();//$this->getConfig(self::API_BASE_URL);
        $data['base_media_url'] = $this->getMediaUrl();//$this->getConfig(self::API_MEDIA_URL);
        $data['catalog_media_url'] = $this->getCatalogMediaUrl();//$this->getConfig(self::API_CATALOG_MEDIA_URL);
        $data['category_media_url'] = $this->getCategoryMediaUrl();//$this->getConfig(self::API_CATEGORY_MEDIA_URL);
        //$data['cart_min_shipping_data'] =$this->getShippingConfigurationData();
        $data['shipping_cart_min_amt'] = $this->_shppingHelper->getCartAmountLimit();
        $data['shipping_cart_product_code'] = $this->getConfig(self::SHIPPING_PRODUCT_CODE);
        $data['shipping_cart_product_sku'] = $this->_shppingHelper->getShippingProductCode();
        return $data;
    }

    /**
     * @return array
     */
    public function getAppFeedbackData(){
        $data = [];
        $data['feedback_email'] = $this->getConfig(self::API_FEEDBACK_EMAIL);
        $data['general_conditions_url'] = $this->getConfig(self::API_GENERAL_CONDITION_URL);
        $data['privacy_policy_url'] = $this->getConfig(self::API_PRIVACY_POLICY_URL);
        return $data;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCatalogMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfigInterface->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCatalogFlipPdfUrl()
    {
        return $this->getConfig(self::FLIP_CATALOG_URL);
    }

    /**
     *
     */
    public function getShippingConfigurationData()
    {
        $data = [];
        $data['shipping_cart_min_amt'] = $this->_shppingHelper->getCartAmountLimit();
        $data['shipping_cart_product_code'] = $this->getConfig(self::SHIPPING_PRODUCT_CODE);
        $data['shipping_cart_product_sku'] = $this->_shppingHelper->getShippingProductCode();
        return $data;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductPdfMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'product_pdfs/';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPlaceHolderUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/default/replacement_product.png';
    }
}

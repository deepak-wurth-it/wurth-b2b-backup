<?php

namespace Wurth\Reportbug\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;

class Reportbug extends View
{
    /**
     * @var UrlInterface
     */
    protected $_urlInterface;
    /**
     * @var SessionFactory
     */
    protected $_customerSessionFactory;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * Reportbug constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param UrlInterface $urlInterface
     * @param SessionFactory $customerSessionFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        UrlInterface $urlInterface,
        SessionFactory $customerSessionFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_urlInterface = $urlInterface;
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_httpContext = $httpContext;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * Get current page url
     *
     * @return string
     */
    public function getCurrentPageUrl()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    /**
     * Get customer data
     *
     * @return Customer
     */
    public function getCustomerData()
    {
        $customer = $this->_customerSessionFactory->create();
        return $customer->getCustomer();
    }

    /**
     * Get login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $this->_urlInterface
            ->getUrl(
                'customer/account/login',
                ['referer' => base64_encode($url)]
            );
    }

    /**
     * Check customer is logged in
     *
     * @return mixed|null
     */
    public function isLoggedIn()
    {
        return $this->_httpContext->getValue(Context::CONTEXT_AUTH);
    }
}

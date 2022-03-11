<?php

namespace Wurth\Shippingproduct\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CART_AMOUNT_LIMIT = "shipping_product_section/general/cart_amount_limit";
    const SHIPPING_PRODUCT_CODE = 'shipping_product_section/general/product_code';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Get Cart amount limit
     *
     * @return mixed
     */
    public function getCartAmountLimit()
    {
        $cartAmountLimit = (float) $this->getConfig(self::CART_AMOUNT_LIMIT);
        if ($cartAmountLimit === '') {
            $cartAmountLimit = 500;
        }
        return $cartAmountLimit;
    }

    /**
     * Get shipping product code
     *
     * @return mixed
     */
    public function getShippingProductCode()
    {
        $shippingProductCode = $this->getConfig(self::SHIPPING_PRODUCT_CODE);
        if ($shippingProductCode === '') {
            $shippingProductCode = "250";
        }
        return $shippingProductCode;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}

<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wcb\Catalog\Block\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use \Pim\Product\Model\ProductFactory as PimProductFactory;
use Magento\Catalog\Block\Product\View as BaseView;


class View extends BaseView
{
    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface|\Magento\Framework\Pricing\PriceCurrencyInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PimProductFactory $pimProductFactory,
        array $data = []
    ) {
        $this->_productHelper = $productHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->pimProductFactory = $pimProductFactory;

        $this->priceCurrency = $priceCurrency;
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

    public function getPackageBox($sku){

        $product =  $this->getProduct();
        if($product){
			return $product->getPackageBox();
        }
    }

     public function getPackaging(){
        //die('ok');
        $product =  $this->getProduct();

        if($product){
			 $minimum_sales_quantity =  (float)$product->getMinimumSalesUnitQuantity();
             $base_unit_of_measure_id = (float) $product->getBaseUnitOfMeasureId();


             if($base_unit_of_measure_id == '2'){

			 return $totalPkg = 	$minimum_sales_quantity * 100;
			}
          return $minimum_sales_quantity;
        }
    }
}

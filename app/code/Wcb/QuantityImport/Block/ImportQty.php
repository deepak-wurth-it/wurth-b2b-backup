<?php

namespace Wcb\QuantityImport\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Wcb\QuantityImport\Model\ResourceModel\QuantityImport\CollectionFactory as QuantityImportCollection;

class ImportQty extends View
{
    protected $quantityImportCollection;

    public function __construct(
        Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        QuantityImportCollection $quantityImportCollection,
        array $data = []
    ) {
        $this->quantityImportCollection = $quantityImportCollection;
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
    public function getImportQtyData()
    {
        $product = $this->getProduct();
        return $this->quantityImportCollection->create()
            ->addFieldToFilter('product_code', ['eq'=>$product->getProductCode()])
            ->setPageSize(3)
            ->setOrder('entity_id', 'ASC');
    }
}

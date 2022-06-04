<?php

namespace Wurth\Shippingproduct\CustomerData;

use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\ObjectManager;
use Wurth\Shippingproduct\Helper\Data as helperData;
use Wcb\Component\Helper\Data as componentHelper;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    private $escaper;
    protected $helperData;
    protected $componentHelper;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        helperData $helperData,
        componentHelper $componentHelper,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null
    ) {
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $this->helperData = $helperData;
        $this->componentHelper = $componentHelper;
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper, $escaper, $itemResolver);
    }

    protected function doGetItemData()
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());
        $isShippingProduct = null;
        if ($this->item->getProduct()->getSku() == $this->helperData->getShippingProductCode()) {
            $isShippingProduct = true;
        }

        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
            'message' => $this->item->getMessage(),
            'is_shipping_product' => $isShippingProduct,
            'is_customer_logged_in' => $this->componentHelper->isLoggedIn(),
            'item_raw_total' => $this->checkoutHelper->formatPrice($this->item->getRowTotal())
        ];
    }
}

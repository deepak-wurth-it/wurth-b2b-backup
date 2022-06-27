<?php

namespace Wcb\Checkout\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Wcb\Checkout\Helper\Data as CheckoutHelper;

class ManageProductStatus extends AbstractHelper
{
    protected $messageManager;
    protected $request;
    protected $productCollectionFactory;
    protected $checkoutHelper;
    protected $multiPriceAndStock;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RequestInterface $request,
        ProductCollection $productCollectionFactory,
        checkoutHelper $checkoutHelper,
        MultiPriceAndStock $multiPriceAndStock
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->multiPriceAndStock = $multiPriceAndStock;
        parent::__construct($context);
    }

    public function checkDiscontinuedProductStatus($product, $qty = 1, $isAjax = false)
    {
        $wcbProductStatus = $product->getWcbProductStatus();
        $replaceProductCode = $product->getSuccessorProductCode();//"039 410";

        $result = [];
        $result['allow_add_to_cart'] = true;
        $result['show_replace_product'] = false;
        $result['replace_product_code'] = $replaceProductCode;
        $result['replacementMsg'] = '';
        $result['notAllowMsg'] = '';

        // If status is 3 then add to cart not allowed and show replacement product
        if ($wcbProductStatus == '3') {
            $result['allow_add_to_cart'] = false;
            $result['show_replace_product'] = true;
        }

        // If status is 2 then check stock availability and show replacement product
        if ($wcbProductStatus == '2') {

            // get total qty with minimum qty logic

            $qty = $this->checkoutHelper->getTotalQty($product, $qty);

            // get stock using API
            $stockSku = [];
            $stockSku['skus'][] = [
                "product_code" => $product->getProductCode(),
                "qty" => 1
            ];
            $stockSku = json_encode($stockSku);
            $stockApiData = $this->multiPriceAndStock->getMultiStockAndPriceData($stockSku, 'stock');

            if (!empty($stockApiData)) {
                $stockApiData = json_decode($stockApiData, true);
                $stockQty = isset($stockApiData[0]['AvailableQuantity'])
                    ? $stockApiData[0]['AvailableQuantity']
                    : 0;
                if ($stockQty < $qty) {
                    $result['allow_add_to_cart'] = false;
                    $result['show_replace_product'] = true;
                }
            }
        }

        //If current product and replacement code are same or blank then not display message.
        if ($replaceProductCode == $product->getProductCode() || !$replaceProductCode) {
            $result['replace_product_code'] = '';
        }

        //Display replacement product message
        if ($result['show_replace_product']) {
            if ($result['replace_product_code']) {
                $replaceProduct = $this->getProductUrlUsingProductCode($result['replace_product_code']);
                $replCode = $result['replace_product_code'];
                if ($replaceProduct->getId()) {
                    $replCodeUrl = $replaceProduct->getProductUrl();
                }
                $link = "<a href='" . $replCodeUrl . "'>$replCode</a>";
                if (!$isAjax) {
                    $this->messageManager->addNotice(__("You are not allowed to add this product."));
                    $this->messageManager->addNotice(
                        sprintf(__("This is replacement product for this %s ."), $link)
                    );
                }
                $result['replacementMsg'] = sprintf(__("This is replacement product for this %s ."), $link);
                $result['notAllowMsg'] = "Not allowed to add this product.";
            } else {
                if (!$isAjax) {
                    $this->messageManager->addNotice(__("You are not allowed to add this product."));
                }
                $result['replacementMsg'] = '';
                $result['notAllowMsg'] = "Not allowed to add this product.";
            }
        }

        return $result;
    }

    /**
     * @param $productCode
     * @return DataObject
     */
    public function getProductUrlUsingProductCode($productCode)
    {
        return $this->productCollectionFactory->create()
            ->addAttributeToFilter("product_code", ["eq" => $productCode])
            ->getFirstItem();
    }
}

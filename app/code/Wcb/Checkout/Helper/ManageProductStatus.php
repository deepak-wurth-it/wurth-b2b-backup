<?php

namespace Wcb\Checkout\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;

class ManageProductStatus extends AbstractHelper
{
    protected $messageManager;
    protected $request;
    protected $productCollectionFactory;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RequestInterface $request,
        ProductCollection $productCollectionFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function checkDiscontinuedProductStatus($product, $throwError = false)
    {
        $wcbProductStatus = $product->getWcbProductStatus();
        $replaceProductCode = $product->getSuccessorProductCode();

        $result = [];
        $result['allow_add_to_cart'] = true;
        $result['show_replace_product'] = false;
        $result['replace_product_code'] = $replaceProductCode;

        // If status is 3 then add to cart not allowed and show replacement product
        if ($wcbProductStatus == '3') {
            $result['allow_add_to_cart'] = false;
            $result['show_replace_product'] = true;
        }

        // If status is 2 then check stock availability and show replacement product
        if ($wcbProductStatus == '2') {
            $result['show_replace_product'] = true;
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
                $this->messageManager->addNotice(
                    sprintf(__("This is replacement product for this %s ."), $link)
                );
            } else {
                $this->messageManager->addNotice(__("You are not allowed to add this product."));
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
